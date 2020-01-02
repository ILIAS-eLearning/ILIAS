<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusTestPassed extends ilLPStatus
{
    public function __construct($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        parent::__construct($a_obj_id);
        $this->db = $ilDB;
    }

    public static function _getInProgress($a_obj_id)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];
        
        $ilBench->start('LearningProgress', '9182_LPStatusTestPassed_inProgress');
        $userIds = self::getUserIdsByResultArrayStatus($a_obj_id, 'in_progress');
        $ilBench->stop('LearningProgress', '9182_LPStatusTestPassed_inProgress');
        
        return $userIds;
    }

    public static function _getCompleted($a_obj_id)
    {
        global $DIC;

        $ilBench = $DIC['ilBench'];

        $ilBench->start('LearningProgress', '9183_LPStatusTestPassed_completed');
        $userIds = self::getUserIdsByResultArrayStatus($a_obj_id, 'passed');
        $ilBench->stop('LearningProgress', '9183_LPStatusTestPassed_completed');

        return $userIds;
    }

    public static function _getNotAttempted($a_obj_id)
    {
        return self::getUserIdsByResultArrayStatus($a_obj_id, 'not_attempted');
    }

    public static function _getFailed($a_obj_id)
    {
        return self::getUserIdsByResultArrayStatus($a_obj_id, 'failed');
    }
    
    private static function getUserIdsByResultArrayStatus($objId, $resultArrayStatus)
    {
        $status_info = ilLPStatusWrapper::_getStatusInfo($objId);
        
        $user_ids = array();
        
        foreach ($status_info['results'] as $user_data) {
            if ($user_data[$resultArrayStatus]) {
                $user_ids[] = $user_data['user_id'];
            }
        }
        
        return $user_ids;
    }

    public static function _getStatusInfo($a_obj_id)
    {
        include_once './Modules/Test/classes/class.ilObjTestAccess.php';
        $status_info['results'] = ilObjTestAccess::_getPassedUsers($a_obj_id);
        return $status_info;
    }
    
    /**
     * Determine status.
     *
     * Behaviour of "old" 4.0 learning progress:
     *
     * Setting "Multiple Pass Scoring": Score the last pass
     * - Test not started: No entry
     * - First question opened: Icon/Text: Failed, Score 0%
     * - First question answered (correct, points enough for passing): Icon/Text: Completed, Score 66%
     * - No change after successfully finishing the pass. (100%)
     * - 2nd Pass, first question opened: Still Completed/Completed
     * - First question answered (incorrect, success possible): Icon/Text Failed, Score 33%
     * - Second question answered (correct): Icon/Text completed
     * - 3rd pass, like 2nd, but two times wrong answer: Icon/Text: Failed
     *
     * Setting "Multiple Pass Scoring": Score the best pass
     * - Test not started: No entry
     * - First question opened: Icon/Text: Failed, Score 0%
     * - First question answered (correct, points enough for passing): Icon/Text: Completed, Score 66%
     * - No change after successfully finishing the pass. (100%)
     * - 2nd Pass, first question opened: Still Completed/Completed
     * - First question answered (incorrect, success possible): Still Completed/Completed
     *
     * Due to this behaviour in 4.0 we do not have a "in progress" status. During the test
     * the status is "failed" unless the score is enough to pass the test, which makes the
     * learning progress status "completed".
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		status
     */
    public function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
        require_once 'Modules/Test/classes/class.ilObjTestAccess.php';
        $res = $ilDB->query("
			SELECT tst_active.active_id, tst_active.tries, count(tst_sequence.active_fi) " . $ilDB->quoteIdentifier("sequences") . ", tst_active.last_finished_pass,
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
			WHERE tst_active.user_fi = {$ilDB->quote($a_user_id, "integer")}
			AND tst_active.test_fi = {$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id))}
			GROUP BY tst_active.active_id, tst_active.tries, is_last_pass
		");

        if ($rec = $ilDB->fetchAssoc($res)) {
            if ($rec['sequences'] > 0) {
                require_once 'Modules/Test/classes/class.ilObjTest.php';

                $test_obj	= new ilObjTest($a_obj_id, false);
                $is_passed	= ilObjTestAccess::_isPassed($a_user_id, $a_obj_id);

                if ($test_obj->getPassScoring() == SCORE_LAST_PASS) {
                    $is_finished	= false;
                    if ($rec['last_finished_pass'] != null && $rec['sequences'] - 1 == $rec['last_finished_pass']) {
                        $is_finished = true;
                    }
                    $status = $this->determineStatusForScoreLastPassTests($is_finished, $is_passed);
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

    /**
     * @param $is_finished
     * @param $passed
     * @return int
     */
    protected function determineStatusForScoreLastPassTests($is_finished, $passed)
    {
        $status = self::LP_STATUS_IN_PROGRESS_NUM;

        if ($is_finished) {
            $status = $this->determineLpStatus($passed);
        }

        return $status;
    }

    /**
     * @param $passed
     * @return int
     */
    protected function determineLpStatus($passed)
    {
        $status = self::LP_STATUS_FAILED_NUM;

        if ($passed) {
            $status = self::LP_STATUS_COMPLETED_NUM;
        }

        return $status;
    }

    /**
     * Determine percentage
     *
     * @param	integer		object id
     * @param	integer		user id
     * @param	object		object (optional depends on object type)
     * @return	integer		percentage
     */
    public function determinePercentage($a_obj_id, $a_user_id, $a_obj = null)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $set = $ilDB->query("SELECT tst_result_cache.*, tst_active.user_fi FROM " .
                     "tst_result_cache JOIN tst_active ON (tst_active.active_id = tst_result_cache.active_fi)" .
                     " JOIN tst_tests ON (tst_tests.test_id = tst_active.test_fi) " .
                     " WHERE tst_tests.obj_fi = " . $ilDB->quote($a_obj_id, "integer") .
                     " AND tst_active.user_fi = " . $ilDB->quote($a_user_id, "integer"));
        $per = 0;
        if ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["max_points"] > 0) {
                $per = min(100, 100 / $rec["max_points"] * $rec["reached_points"]);
            } else {
                // According to mantis #12305
                $per = 0;
            }
        }
        return (int) $per;
    }
}
