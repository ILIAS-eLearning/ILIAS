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

	function ilLPStatusTestPassed($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getInProgress($a_obj_id)
	{
		global $ilDB;

		global $ilBench;
		$ilBench->start('LearningProgress','9182_LPStatusTestPassed_inProgress');


		include_once './Modules/Test/classes/class.ilObjTestAccess.php';

		$query = "SELECT DISTINCT(user_fi) FROM tst_active ".
			"WHERE test_fi = '".ilObjTestAccess::_getTestIDFromObjectID($a_obj_id)."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_fi;
		}

		$users = array_diff((array) $user_ids,ilLPStatusWrapper::_getCompleted($a_obj_id));
		$users = array_diff((array) $users,ilLPStatusWrapper::_getFailed($a_obj_id));
		$users = array_diff((array) $users,ilLPStatusWrapper::_getNotAttempted($a_obj_id));

		$ilBench->stop('LearningProgress','9182_LPStatusTestPassed_inProgress');
		return $users ? $users : array();
	}

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		global $ilBench;
		$ilBench->start('LearningProgress','9183_LPStatusTestPassed_completed');

		include_once './Modules/Test/classes/class.ilObjTestAccess.php';
		
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		foreach($status_info['results'] as $user_data)
		{
			if($user_data['passed'])
			{
				$user_ids[] = $user_data['user_id'];
			}
		}

		$ilBench->stop('LearningProgress','9183_LPStatusTestPassed_completed');
		return $user_ids ? $user_ids : array();
	}

	function _getNotAttempted($a_obj_id)
	{
		$user_ids = array();

		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);

		foreach($status_info['results'] as $user_data)
		{
			if( !$user_data['failed'] && !$user_data['passed'] )
			{
				$user_ids[] = $user_data['user_id'];
			}
		}
		return $user_ids;
	}

	function _getFailed($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		foreach($status_info['results'] as $user_data)
		{
			if($user_data['failed'])
			{
				$user_ids[] = $user_data['user_id'];
			}
		}
		return $user_ids ? $user_ids : array();
	}

	function _getStatusInfo($a_obj_id)
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
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		global $ilObjDataCache, $ilDB, $ilLog;
		
		$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
		
		include_once './Modules/Test/classes/class.ilObjTestAccess.php';

		$res = $ilDB->query("
			SELECT tst_active.active_id, tst_active.tries, count(tst_sequence.active_fi) sequences
			FROM tst_active
			LEFT JOIN tst_sequence
			ON tst_sequence.active_fi = tst_active.active_id
			WHERE tst_active.user_fi = {$ilDB->quote($a_user_id, "integer")}
			AND tst_active.test_fi = {$ilDB->quote(ilObjTestAccess::_getTestIDFromObjectID($a_obj_id))}
			GROUP BY tst_active.active_id, tst_active.tries
		");
		
		if ($rec = $ilDB->fetchAssoc($res))
		{
			if( $rec['sequences'] > 0 )
			{
				include_once './Modules/Test/classes/class.ilObjTestAccess.php';
				if (ilObjTestAccess::_isPassed($a_user_id, $a_obj_id))
				{
					$status = self::LP_STATUS_COMPLETED_NUM;
				}
				else
				{
					$status = self::LP_STATUS_FAILED_NUM;
				}
			}
			else
			{
				$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
			}
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
	function determinePercentage($a_obj_id, $a_user_id, $a_obj = null)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT tst_result_cache.*, tst_active.user_fi FROM ".
					 "tst_result_cache JOIN tst_active ON (tst_active.active_id = tst_result_cache.active_fi)".
					 " JOIN tst_tests ON (tst_tests.test_id = tst_active.test_fi) ".
					 " WHERE tst_tests.obj_fi = ".$ilDB->quote($a_obj_id, "integer").
					 " AND tst_active.user_fi = ".$ilDB->quote($a_user_id, "integer"));
		$per = 0;
		if ($rec = $ilDB->fetchAssoc($set))
		{	
			if ($rec["max_points"] > 0)
			{
				$per = min(100, 100 / $rec["max_points"] * $rec["reached_points"]);
			}
			else
			{
				// According to mantis #12305
				$per = 0;
			}
		}
		return (int) $per;
	}


}	
?>