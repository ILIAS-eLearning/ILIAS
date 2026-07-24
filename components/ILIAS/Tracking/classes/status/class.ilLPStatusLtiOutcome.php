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
            $object = ilObjectFactory::getInstanceByObjId($objId);
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
            $activityProgress = (string) ($latestGrade['activity_progress'] ?? '');
            $gradingProgress = (string) ($latestGrade['grading_progress'] ?? '');

            if ($gradingProgress === 'Failed') {
                return self::LP_STATUS_FAILED_NUM;
            }

            if (in_array($activityProgress, ['Started', 'InProgress'], true) ||
                ($activityProgress === 'Submitted' && $gradingProgress !== 'FullyGraded') ||
                in_array($gradingProgress, ['Pending', 'PendingManual', 'NotReady'], true)) {
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
            return (int) round(((float) $latestGrade['score_given'] / (float) $latestGrade['score_maximum']) * 100);
        }

        $ltiResult = $this->getLtiUserResult($a_obj_id, $a_usr_id);

        if ($ltiResult instanceof ilLTIConsumerResult) {
            return (int) $ltiResult->getResult() * 100;
        }

        return 0;
    }
}
