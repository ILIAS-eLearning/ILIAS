<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * Reports the learning progress of the users who came through LTI back to the platform they came from.
 * It works for both LTI versions: which service is used depends on the resource link, as celtic/lti
 * decides it from the data of the link.
 *
 * The name is fixed: module.xml registers it for the events of Tracking, and the SCORM components call
 * handleOutcomeWithoutLP() directly.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAppEventListener implements ilAppEventListener
{
    /**
     * Prefix of the authentication mode of a user created by an LTI launch, followed by the platform id.
     */
    private const string AUTH_MODE_PREFIX = 'lti_';

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        if ($a_component !== 'components/ILIAS/Tracking' || $a_event !== 'updateStatus') {
            return;
        }

        new self()->reportStatus(
            (int) $a_parameter['obj_id'],
            (int) $a_parameter['usr_id'],
            (int) $a_parameter['status'],
            (int) $a_parameter['percentage']
        );
    }

    /**
     * Reports the score of an object without learning progress, which SCORM modules of a single SCO
     * send on their own.
     */
    public static function handleOutcomeWithoutLP(int $a_obj_id, int $a_usr_id, ?float $a_percentage): void
    {
        if (ilObjectLP::getInstance($a_obj_id)->getCurrentMode() !== ilLPObjSettings::LP_MODE_DEACTIVATED) {
            return;
        }

        $listener = new self();
        $user = $listener->getLtiUser($a_usr_id);
        if ($user === null) {
            return;
        }

        $score = $a_percentage > 0 ? round($a_percentage / 100, 4) : 0.0;
        foreach (ilObject::_getAllReferences($a_obj_id) as $ref_id) {
            foreach ($listener->getResourceLinks((int) $ref_id, $user['account'], $user['platform']) as $resource_link) {
                $listener->sendOutcome($resource_link, $user['account'], $score, null);
            }
        }
    }

    /**
     * Reports the current status of the LTI users whose resource links changed since the given date,
     * which also covers changes of the learning progress settings that raise no event.
     */
    public static function reportChangesSince(ilDateTime $since): void
    {
        global $DIC;

        $listener = new self();
        $db = $DIC->database();
        $result = $db->query(
            'SELECT ur.lti_user_id, rl.resource_link_pk, c.ext_consumer_id, c.ref_id'
            . ' FROM lti2_resource_link rl'
            . ' JOIN lti2_user_result ur ON ur.resource_link_pk = rl.resource_link_pk'
            . ' JOIN lti2_consumer c ON c.consumer_pk = rl.consumer_pk'
            . ' WHERE c.enabled = ' . $db->quote(1, 'integer')
            . ' AND rl.updated > ' . $db->quote($since->get(IL_CAL_DATETIME), 'timestamp')
        );

        while ($row = $db->fetchAssoc($result)) {
            $login = ilObjUser::_checkExternalAuthAccount(self::AUTH_MODE_PREFIX . $row['ext_consumer_id'], $row['lti_user_id']);
            if (!$login) {
                continue;
            }

            $usr_id = ilObjUser::_lookupId($login);
            $obj_id = ilObject::_lookupObjId((int) $row['ref_id']);
            $status = (int) ilLPStatus::_lookupStatus($obj_id, $usr_id);
            $percentage = $listener->getPercentage($obj_id, $status, (int) ilLPStatus::_lookupPercentage($obj_id, $usr_id));
            $listener->sendOutcome((int) $row['resource_link_pk'], $row['lti_user_id'], $listener->getScore($status, $percentage), $status);
        }
    }

    private function reportStatus(int $obj_id, int $usr_id, int $status, int $percentage): void
    {
        $user = $this->getLtiUser($usr_id);
        if ($user === null) {
            return;
        }

        $score = $this->getScore($status, $this->getPercentage($obj_id, $status, $percentage));
        foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
            foreach ($this->getResourceLinks((int) $ref_id, $user['account'], $user['platform']) as $resource_link) {
                $this->sendOutcome($resource_link, $user['account'], $score, $status);
            }
        }
    }

    /**
     * @return array|null the id of the user at the platform ('account') and the platform ('platform'),
     *                    null when the user did not come through LTI
     */
    private function getLtiUser(int $usr_id): ?array
    {
        $auth_mode = ilObjUser::_lookupAuthMode($usr_id);
        if (!str_starts_with($auth_mode, self::AUTH_MODE_PREFIX)) {
            return null;
        }

        return [
            'account' => ilObjUser::_lookupExternalAccount($usr_id),
            'platform' => (int) substr($auth_mode, strlen(self::AUTH_MODE_PREFIX)),
        ];
    }

    /**
     * @return array the resource links through which the user launched the object from the platform
     */
    private function getResourceLinks(int $ref_id, string $account, int $platform_id): array
    {
        global $DIC;

        $db = $DIC->database();
        $result = $db->queryF(
            'SELECT rl.resource_link_pk FROM lti2_user_result ur'
            . ' JOIN lti2_resource_link rl ON rl.resource_link_pk = ur.resource_link_pk'
            . ' JOIN lti2_consumer c ON c.consumer_pk = rl.consumer_pk'
            . ' WHERE c.enabled = %s AND c.ref_id = %s AND ur.lti_user_id = %s AND c.ext_consumer_id = %s',
            ['integer', 'integer', 'text', 'integer'],
            [1, $ref_id, $account, $platform_id]
        );

        $resource_links = [];
        while ($row = $db->fetchAssoc($result)) {
            $resource_links[] = (int) $row['resource_link_pk'];
        }

        return $resource_links;
    }

    /**
     * A course or a group has no percentage of its own, so being done with it counts as full score.
     */
    private function getPercentage(int $obj_id, int $status, int $percentage): int
    {
        $done = $status === ilLPStatus::LP_STATUS_COMPLETED_NUM || $status === ilLPStatus::LP_STATUS_FAILED_NUM;

        return $done && in_array(ilObject::_lookupType($obj_id), ['crs', 'grp'], true) ? 100 : $percentage;
    }

    private function getScore(int $status, int $percentage): ?float
    {
        return $status === ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM ? null : $percentage / 100;
    }

    /**
     * Sends the score through the outcome service of the resource link, with the progress the status
     * stands for when there is one (LTI Advantage Assignment and Grade Services).
     *
     * The resource link is read through the data connector of ILIAS as a tool, which comes with the
     * launch of ILIAS as a tool. Until then nothing is sent.
     */
    private function sendOutcome(int $resource_link, string $account, ?float $score, ?int $status): void
    {
        global $DIC;

        $DIC->logger()->root()->debug(sprintf(
            'LTI outcome for resource link %d and user %s not sent (score %s, status %s): ILIAS as a tool cannot read resource links yet.',
            $resource_link,
            $account,
            var_export($score, true),
            var_export($status, true)
        ));
    }
}
