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

declare(strict_types=0);

/**
 * Class ilLPStatusLtiOutcome
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 */
class ilLPStatusLtiOutcome extends ilLPStatus
{
    private static array $userResultCache = array();
    private static array $objectCache = array();

    private static function getUsersWithLtiData(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $usr_ids = array();
        $query = 'SELECT DISTINCT usr_id FROM lti_consumer_results'
            . ' WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            $usr_ids[] = (int) $row['usr_id'];
        }

        if ($ilDB->tableExists('lti_consumer_grades')) {
            $query = 'SELECT DISTINCT usr_id FROM lti_consumer_grades'
                . ' WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');

            $res = $ilDB->query($query);
            while ($row = $ilDB->fetchAssoc($res)) {
                $usr_ids[] = (int) $row['usr_id'];
            }
        }

        return array_values(array_unique($usr_ids));
    }

    private static function getUsersByStatus(int $a_obj_id, int $a_status): array
    {
        $usr_ids = array();
        $lp_status = new self($a_obj_id);
        $object = self::getObject($a_obj_id);

        foreach (self::getUsersWithLtiData($a_obj_id) as $usr_id) {
            if ($lp_status->determineStatus($a_obj_id, $usr_id, $object) === $a_status) {
                $usr_ids[] = $usr_id;
            }
        }

        return $usr_ids;
    }

    private static function getObject(int $objId): ilObjLTIConsumer
    {
        if (!isset(self::$objectCache[$objId])) {
            self::$objectCache[$objId] = ilObjectFactory::getInstanceByObjId($objId);
        }

        return self::$objectCache[$objId];
    }

    public static function _getInProgress(int $a_obj_id): array
    {
        return self::getUsersByStatus($a_obj_id, self::LP_STATUS_IN_PROGRESS_NUM);
    }

    public static function _getCompleted(int $a_obj_id): array
    {
        return self::getUsersByStatus($a_obj_id, self::LP_STATUS_COMPLETED_NUM);
    }

    public static function _getFailed(int $a_obj_id): array
    {
        return self::getUsersByStatus($a_obj_id, self::LP_STATUS_FAILED_NUM);
    }

    public static function _getNotAttempted(int $a_obj_id): array
    {
        $members = ilObjectLP::getInstance($a_obj_id)->getMembers();
        if (!$members) {
            return array();
        }

        $users = array_diff((array) $members, self::_getInProgress($a_obj_id));
        $users = array_diff($users, self::_getCompleted($a_obj_id));
        $users = array_diff($users, self::_getFailed($a_obj_id));

        return $users;
    }

    private function getLtiUserResult(
        int $objId,
        int $usrId
    ): ?ilLTIConsumerResult {
        if (!isset(self::$userResultCache[$objId])) {
            self::$userResultCache[$objId] = array();
        }

        if (!isset(self::$userResultCache[$objId][$usrId])) {
            $ltiUserResult = ilLTIConsumerResult::getByKeys($objId, $usrId);
            self::$userResultCache[$objId][$usrId] = $ltiUserResult;
        }
        return self::$userResultCache[$objId][$usrId];
    }

    private function ensureObject(int $objId, $object): ilObjLTIConsumer
    {
        if (!($object instanceof ilObjLTIConsumer)) {
            $object = self::getObject($objId);
        }
        return $object;
    }

    private function getLatestAgsGrade(int $objId, int $usrId): ?array
    {
        global $DIC;

        $db = $DIC->database();
        if (!$db->tableExists('lti_consumer_grades')) {
            return null;
        }

        $query = 'SELECT * FROM lti_consumer_grades'
            . ' WHERE obj_id = ' . $db->quote($objId, 'integer')
            . ' AND usr_id = ' . $db->quote($usrId, 'integer')
            . ' ORDER BY lti_timestamp DESC, stored DESC, id DESC';
        $res = $db->query($query);
        $row = $db->fetchAssoc($res);

        return $row ?: null;
    }

    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        global $DIC;
        $logger = $DIC->logger()->root();

        $latestGrade = $this->getLatestAgsGrade($a_obj_id, $a_usr_id);
        if ($latestGrade !== null) {
            $activityProgress = ilLTIConsumerActivityProgress::tryFrom((string) ($latestGrade['activity_progress'] ?? ''));
            $gradingProgress = ilLTIConsumerGradingProgress::tryFrom((string) ($latestGrade['grading_progress'] ?? ''));

            if (($activityProgress?->isInProgress() ?? false) ||
                ($activityProgress === ilLTIConsumerActivityProgress::SUBMITTED && $gradingProgress?->isPending()) ||
                ($gradingProgress?->isPending() ?? false)) {
                return self::LP_STATUS_IN_PROGRESS_NUM;
            }
        }

        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            $object = $this->ensureObject($a_obj_id, $a_obj);
            $ltiMasteryScore = $object->getMasteryScore();

            if ($ltiResult->getResult() >= $ltiMasteryScore) {
                return self::LP_STATUS_COMPLETED_NUM;
            }

            return self::LP_STATUS_IN_PROGRESS_NUM;
        }

        return self::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ): int {
        $latestGrade = $this->getLatestAgsGrade($a_obj_id, $a_usr_id);
        if ($latestGrade !== null &&
            is_numeric($latestGrade['score_given'] ?? null) &&
            is_numeric($latestGrade['score_maximum'] ?? null) &&
            (float) $latestGrade['score_maximum'] > 0) {
            return max(0, min(100, (int) round(
                ((float) $latestGrade['score_given'] / (float) $latestGrade['score_maximum']) * 100
            )));
        }

        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            return (int) round((float) $ltiResult->getResult() * 100);
        }

        return 0;
    }
}
