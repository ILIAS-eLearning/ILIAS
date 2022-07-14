<?php declare(strict_types=0);

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

/**
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-tracking
 */
class ilLPStatusTestPassed extends ilLPStatus
{
    public static function _getInProgress(int $a_obj_id) : array
    {
        $userIds = self::getUserIdsByResultArrayStatus(
            $a_obj_id,
            'in_progress'
        );
        return $userIds;
    }

    public static function _getCompleted(int $a_obj_id) : array
    {
        $userIds = self::getUserIdsByResultArrayStatus($a_obj_id, 'passed');
        return $userIds;
    }

    public static function _getNotAttempted(int $a_obj_id) : array
    {
        return self::getUserIdsByResultArrayStatus($a_obj_id, 'not_attempted');
    }

    public static function _getFailed(int $a_obj_id) : array
    {
        return self::getUserIdsByResultArrayStatus($a_obj_id, 'failed');
    }

    private static function getUserIdsByResultArrayStatus(
        $objId,
        $resultArrayStatus
    ) {
        $status_info = ilLPStatusWrapper::_getStatusInfo($objId);

        $user_ids = array();

        foreach ($status_info['results'] as $user_data) {
            if (isset($user_data[$resultArrayStatus]) && $user_data[$resultArrayStatus]) {
                $user_ids[] = (int) $user_data['user_id'];
            }
        }

        return $user_ids;
    }

    public static function _getStatusInfo(int $a_obj_id) : array
    {
        $status_info['results'] = ilObjTestAccess::_getPassedUsers($a_obj_id);
        return $status_info;
    }

    /**
     * Determine status.
     * Behaviour of "old" 4.0 learning progress:
     * Setting "Multiple Pass Scoring": Score the last pass
     * - Test not started: No entry
     * - First question opened: Icon/Text: Failed, Score 0%
     * - First question answered (correct, points enough for passing): Icon/Text: Completed, Score 66%
     * - No change after successfully finishing the pass. (100%)
     * - 2nd Pass, first question opened: Still Completed/Completed
     * - First question answered (incorrect, success possible): Icon/Text Failed, Score 33%
     * - Second question answered (correct): Icon/Text completed
     * - 3rd pass, like 2nd, but two times wrong answer: Icon/Text: Failed
     * Setting "Multiple Pass Scoring": Score the best pass
     * - Test not started: No entry
     * - First question opened: Icon/Text: Failed, Score 0%
     * - First question answered (correct, points enough for passing): Icon/Text: Completed, Score 66%
     * - No change after successfully finishing the pass. (100%)
     * - 2nd Pass, first question opened: Still Completed/Completed
     * - First question answered (incorrect, success possible): Still Completed/Completed
     * Due to this behaviour in 4.0 we do not have a "in progress" status. During the test
     * the status is "failed" unless the score is enough to pass the test, which makes the
     * learning progress status "completed".
     */
    public function determineStatus(
        int $a_obj_id,
        int $a_usr_id,
        object $a_obj = null
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        $res = $this->db->query(
            "
			SELECT tst_active.active_id, tst_active.tries, count(tst_sequence.active_fi) " . $this->db->quoteIdentifier(
                "sequences"
            ) . ", tst_active.last_finished_pass,
				CASE WHEN
					(tst_tests.nr_of_tries - 1) = tst_active.last_finished_pass
				THEN '1'
				ELSE '0'
				END is_last_pass
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			LEFT JOIN tst_tests
			ON tst_tests.test_id = tst_active.test_fi
			WHERE tst_active.user_fi = {$this->db->quote($a_usr_id, "integer")}
			AND tst_active.test_fi = {$this->db->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id), ilDBConstants::T_INTEGER)}
			GROUP BY tst_active.active_id, tst_active.tries, is_last_pass
		"
        );

        if ($rec = $this->db->fetchAssoc($res)) {
            if ($rec['sequences'] > 0) {
                $test_obj = new ilObjTest($a_obj_id, false);
                $is_passed = ilObjTestAccess::_isPassed($a_usr_id, $a_obj_id);

                if ($test_obj->getPassScoring() == SCORE_LAST_PASS) {
                    $is_finished = false;
                    if ($rec['last_finished_pass'] != null && $rec['sequences'] - 1 == $rec['last_finished_pass']) {
                        $is_finished = true;
                    }
                    $status = $this->determineStatusForScoreLastPassTests(
                        $is_finished,
                        $is_passed
                    );
                } elseif ($test_obj->getPassScoring() == SCORE_BEST_PASS) {
                    $status = self::LP_STATUS_IN_PROGRESS_NUM;

                    if ($rec['last_finished_pass'] != null) {
                        $status = $this->determineLpStatus($is_passed);
                    }

                    if (!$rec['is_last_pass'] && $status == self::LP_STATUS_FAILED_NUM) {
                        $status = self::LP_STATUS_IN_PROGRESS_NUM;
                    }
                }
            }
        }

        return $status;
    }

    protected function determineStatusForScoreLastPassTests(
        bool $is_finished,
        bool $passed
    ) : int {
        $status = self::LP_STATUS_IN_PROGRESS_NUM;

        if ($is_finished) {
            $status = $this->determineLpStatus($passed);
        }

        return $status;
    }

    protected function determineLpStatus(bool $passed) : int
    {
        $status = self::LP_STATUS_FAILED_NUM;

        if ($passed) {
            $status = self::LP_STATUS_COMPLETED_NUM;
        }

        return $status;
    }

    public function determinePercentage(
        int $a_obj_id,
        int $a_usr_id,
        ?object $a_obj = null
    ) : int {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $this->db->query(
            "SELECT tst_result_cache.*, tst_active.user_fi FROM " .
            "tst_result_cache JOIN tst_active ON (tst_active.active_id = tst_result_cache.active_fi)" .
            " JOIN tst_tests ON (tst_tests.test_id = tst_active.test_fi) " .
            " WHERE tst_tests.obj_fi = " . $this->db->quote(
                $a_obj_id,
                "integer"
            ) .
            " AND tst_active.user_fi = " . $this->db->quote(
                $a_usr_id,
                "integer"
            )
        );
        $per = 0;
        if ($rec = $this->db->fetchAssoc($set)) {
            if ($rec["max_points"] > 0) {
                $per = min(
                    100,
                    100 / $rec["max_points"] * $rec["reached_points"]
                );
            } else {
                // According to mantis #12305
                $per = 0;
            }
        }
        return (int) $per;
    }
}
