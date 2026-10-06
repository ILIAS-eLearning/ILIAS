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

use ceLTIc\LTI\Enum\LtiVersion;
use ceLTIc\LTI\Enum\ServiceAction;
use ceLTIc\LTI\LineItem;
use ceLTIc\LTI\Outcome;
use ceLTIc\LTI\ResourceLink;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\UserResult;
use Random\RandomException;

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
     * The setting of a resource link that names the object it launched.
     */
    private const string SETTING_REF_ID = 'ilias_ref_id';

    /**
     * The setting of a resource link with the gradebook column of the Assignment and Grade Services, as
     * celtic/lti reads it from the launch.
     */
    private const string SETTING_LINE_ITEM = 'custom_lineitem_url';

    /**
     * @throws RandomException
     */
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
     *
     * @throws RandomException
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
            foreach ($listener->getResourceLinks($ref_id, $user['account'], $user['platform']) as $resource_link) {
                $listener->sendOutcome($resource_link, $ref_id, $user['account'], $score, null);
            }
        }
    }

    /**
     * Keeps the object a resource link launched. The registration of an LTI Advantage platform is not for
     * one object, as the registrations of earlier releases were, so its links name the object themselves.
     */
    public static function rememberObject(?ResourceLink $resource_link, int $ref_id): void
    {
        if ($resource_link === null || $resource_link->getSetting(self::SETTING_REF_ID) === (string) $ref_id) {
            return;
        }
        $resource_link->setSetting(self::SETTING_REF_ID, (string) $ref_id);
        $resource_link->save();
    }

    /**
     * Reports the learning progress of the LTI users whose status changed since the given date. Tracking
     * raises no event when it recalculates the status, as it does after the learning progress settings of
     * an object change; the other changes were reported by their event already, so a score the platform
     * has is not sent again.
     *
     * @throws RandomException
     */
    public static function reportChangesSince(ilDateTime $since): void
    {
        global $DIC;

        $listener = new self();
        $sent = 0;
        $db = $DIC->database();
        $result = $db->query(
            'SELECT m.obj_id, m.usr_id, m.status, m.percentage FROM ut_lp_marks m'
            . ' JOIN usr_data u ON u.usr_id = m.usr_id'
            . ' WHERE m.status_changed > ' . $db->quote($since->get(IL_CAL_DATETIME), 'timestamp')
            . ' AND ' . $db->like('u.auth_mode', 'text', ilAuthProviderLTI::AUTH_MODE_PREFIX . '%', false)
        );
        while ($row = $db->fetchAssoc($result)) {
            $sent += $listener->reportStatus(
                (int) $row['obj_id'],
                (int) $row['usr_id'],
                (int) $row['status'],
                (int) $row['percentage'],
                true
            );
        }

        $DIC->logger()->forComponent('lti')->info('LTI outcomes of the changes since {since} sent to the platforms: {sent}', [
            'since' => $since->get(IL_CAL_DATETIME),
            'sent' => $sent,
        ]);
    }

    /**
     * @param bool $only_changes true to leave out the links whose platform has the score already
     *
     * @return int the number of outcomes the platforms accepted
     *
     * @throws RandomException
     */
    private function reportStatus(int $obj_id, int $usr_id, int $status, int $percentage, bool $only_changes = false): int
    {
        $user = $this->getLtiUser($usr_id);
        if ($user === null) {
            return 0;
        }

        $sent = 0;
        $score = $this->getScore($status, $this->getPercentage($obj_id, $status, $percentage));
        foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
            foreach ($this->getResourceLinks($ref_id, $user['account'], $user['platform']) as $resource_link) {
                $sent += (int) $this->sendOutcome($resource_link, $ref_id, $user['account'], $score, $status, $only_changes);
            }
        }

        return $sent;
    }

    /**
     * @return array|null the id of the user at the platform ('account') and the platform ('platform'),
     *                    null when the user did not come through LTI
     */
    private function getLtiUser(int $usr_id): ?array
    {
        $auth_mode = ilObjUser::_lookupAuthMode($usr_id);
        if (!str_starts_with($auth_mode, ilAuthProviderLTI::AUTH_MODE_PREFIX)) {
            return null;
        }

        return [
            'account' => ilObjUser::_lookupExternalAccount($usr_id),
            'platform' => (int) substr($auth_mode, strlen(ilAuthProviderLTI::AUTH_MODE_PREFIX)),
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
            'SELECT rl.resource_link_pk, c.ref_id FROM lti2_user_result ur'
            . ' JOIN lti2_resource_link rl ON rl.resource_link_pk = ur.resource_link_pk'
            . ' JOIN lti2_consumer c ON c.consumer_pk = rl.consumer_pk'
            . ' WHERE c.enabled = %s AND c.ref_id IN (0, %s) AND ur.lti_user_id = %s AND c.ext_consumer_id = %s',
            ['integer', 'integer', 'text', 'integer'],
            [1, $ref_id, $account, $platform_id]
        );

        $resource_links = [];
        while ($row = $db->fetchAssoc($result)) {
            $resource_link = (int) $row['resource_link_pk'];
            if ((int) $row['ref_id'] === $ref_id || $this->getObjectOfLink($resource_link) === $ref_id) {
                $resource_links[] = $resource_link;
            }
        }

        return $resource_links;
    }

    /**
     * The object a link of the registration of an LTI Advantage platform launched, 0 when it names none.
     */
    private function getObjectOfLink(int $resource_link): int
    {
        return (int) ResourceLink::fromRecordId($resource_link, new ilLTIDataConnector())->getSetting(self::SETTING_REF_ID);
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
     * The Assignment and Grade Services report how far the user is besides the score; the LTI 1.1
     * outcome service only sends the score. Without a status the library reports a completed result.
     *
     * @return array{0: ilLTIToolActivityProgress, 1: ilLTIToolGradingProgress}
     */
    private function getProgress(?int $status): array
    {
        return match ($status) {
            null, ilLPStatus::LP_STATUS_COMPLETED_NUM => [ilLTIToolActivityProgress::COMPLETED, ilLTIToolGradingProgress::FULLY_GRADED],
            ilLPStatus::LP_STATUS_FAILED_NUM => [ilLTIToolActivityProgress::COMPLETED, ilLTIToolGradingProgress::FAILED],
            ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM => [ilLTIToolActivityProgress::INITIALIZED, ilLTIToolGradingProgress::NOT_READY],
            default => [ilLTIToolActivityProgress::IN_PROGRESS, ilLTIToolGradingProgress::PENDING],
        };
    }

    /**
     * Sends the score through the outcome service of the resource link, which celtic/lti picks from what
     * the platform offered at the launch. Nothing is sent before the user has a result.
     *
     * @param bool $only_changes true to send nothing when the platform has the score already
     *
     * @return bool true when the platform accepted the outcome
     *
     * @throws RandomException
     */
    private function sendOutcome(
        int $resource_link,
        int $ref_id,
        string $account,
        ?float $score,
        ?int $status,
        bool $only_changes = false
    ): bool {
        if ($score === null) {
            return false;
        }

        $link = ResourceLink::fromRecordId($resource_link, new ilLTIDataConnector());
        // writing a score only needs the score scope, while hasOutcomesService() also asks for the result scope
        if (!$link->hasOutcomesService() && !$link->hasScoreService() && !$link->hasLineItemService()) {
            return false;
        }
        if ($link->getPlatform()->ltiVersion === LtiVersion::V1P3) {
            if (!ilLTIAdvantageKeyPair::signAsDefaultTool(new Tool(new ilLTIDataConnector()))) {
                return false;
            }
            $this->useLineItemOfObject($link, $ref_id);
        }
        if (!$link->hasOutcomesService() && !$link->hasScoreService()) {
            return false;
        }

        global $DIC;

        $user = UserResult::fromResourceLink($link, $account);
        if ($only_changes && $this->hasScore($link, $user, $score, $status)) {
            return false;
        }

        [$activity_progress, $grading_progress] = $this->getProgress($status);
        $outcome = new Outcome($score, 1, $activity_progress->value, $grading_progress->value);
        $sent = $link->doOutcomesService(ServiceAction::Write, $outcome, $user);
        // the call itself, with what the platform answered, is in the log of the library
        $DIC->logger()->forComponent('lti')->log(
            ($sent ? 'LTI outcome sent' : 'LTI outcome not accepted by the platform') . ': {request}',
            $sent ? ilLogLevel::INFO : ilLogLevel::WARNING,
            ['request' => ilLTILibraryLogger::describe([
                'score' => $score,
                'activity' => $activity_progress->value,
                'grading' => $grading_progress->value,
                'version' => $link->getPlatform()->ltiVersion?->value,
                'registration' => $link->getPlatform()->getRecordId(),
                'resource_link' => $resource_link,
                'resource_link_id' => $link->ltiResourceLinkId,
                'user_id' => $account,
            ])]
        );

        return $sent;
    }

    /**
     * Gives the link the gradebook column of the object when the launch gave none but the platform lets
     * ILIAS manage the columns: the column ILIAS created for the object before, or a new one. The link keeps
     * it as if the launch had given it.
     */
    private function useLineItemOfObject(ResourceLink $link, int $ref_id): void
    {
        if ($link->getSetting(self::SETTING_LINE_ITEM) !== '' || !$link->hasLineItemService()) {
            return;
        }

        global $DIC;

        $log = $DIC->logger()->forComponent('lti');
        $request = [
            'registration' => $link->getPlatform()->getRecordId(),
            'resource_link_id' => $link->ltiResourceLinkId,
            'ref_id' => $ref_id,
        ];
        $line_items = $link->getLineItems((string) $ref_id);
        $line_item = is_array($line_items) && $line_items !== [] ? reset($line_items) : null;
        if ($line_item === null) {
            $line_item = new LineItem($link->getPlatform(), ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id)), 1);
            $line_item->resourceId = (string) $ref_id;
            if (!$link->createLineItem($line_item) || $line_item->endpoint === null) {
                $log->warning('LTI line item not created by the platform: {request}', [
                    'request' => ilLTILibraryLogger::describe($request),
                ]);
                return;
            }
            $log->info('LTI line item created: {request}', [
                'request' => ilLTILibraryLogger::describe($request + ['line_item' => $line_item->endpoint]),
            ]);
        }

        $link->setSetting(self::SETTING_LINE_ITEM, $line_item->endpoint);
        $link->save();
    }

    /**
     * True when the platform has the score of a final status already. A result holds no progress, so a
     * status that is not final is always sent.
     */
    private function hasScore(ResourceLink $link, UserResult $user, float $score, ?int $status): bool
    {
        if (!in_array($status, [ilLPStatus::LP_STATUS_COMPLETED_NUM, ilLPStatus::LP_STATUS_FAILED_NUM], true)) {
            return false;
        }

        $current = new Outcome();
        if (!$link->doOutcomesService(ServiceAction::Read, $current, $user)) {
            return false;
        }
        $value = $current->getValue();
        $maximum = (float) $current->getPointsPossible();

        return is_numeric($value) && $maximum > 0 && abs((float) $value / $maximum - $score) < 0.00005;
    }
}
