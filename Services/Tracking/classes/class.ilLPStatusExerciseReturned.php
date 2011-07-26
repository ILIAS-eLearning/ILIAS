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

include_once 'Services/Tracking/classes/class.ilLPStatus.php';
include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
include_once 'Services/Tracking/classes/class.ilLearningProgress.php';

class ilLPStatusExerciseReturned extends ilLPStatus
{

	function ilLPStatusVisits($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getNotAttempted($a_obj_id)
	{
		// All members
		include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';

		$members = ilExerciseMembers::_getMembers($a_obj_id);

		$users = array_diff($members,$inp = ilLPStatusWrapper::_getInProgress($a_obj_id));
		$users = array_diff($users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));
		$users = array_diff($users,$fai = ilLPStatusWrapper::_getFailed($a_obj_id));


		return $users ? $users : array();
	}

	function _getInProgress($a_obj_id)
	{
		global $ilDB;

		include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$users = ilExerciseMembers::_getReturned($a_obj_id);
		$all = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		$users = $users + $all;

		$users = array_diff($users,$com = ilLPStatusWrapper::_getCompleted($a_obj_id));
		$users = array_diff($users,$fai = ilLPStatusWrapper::_getFailed($a_obj_id));

		return $users ? $users : array();
	}		

	function _getCompleted($a_obj_id)
	{
		global $ilDB;

		include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
		return ($ret = ilExerciseMembers::_getPassedUsers($a_obj_id)) ? $ret : array();
	}

	function _getFailed($a_obj_id)
	{
		include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
		$failed = ilExerciseMembers::_getFailedUsers($a_obj_id);
		return $failed ? $failed : array();
	}

	/**
	 * Determine status
	 *
	 * @param	integer		object id
	 * @param	integer		user id
	 * @param	object		object (optional depends on object type)
	 * @return	integer		status
	 */
	function determineStatus($a_obj_id, $a_user_id, $a_obj = null)
	{
		global $ilObjDataCache, $ilDB;
		
		$status = LP_STATUS_NOT_ATTEMPTED_NUM;
		switch ($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'exc':
				include_once './Services/Tracking/classes/class.ilChangeEvent.php';
				include_once './Modules/Exercise/classes/class.ilExerciseMembers.php';
				if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id) ||
					ilExerciseMembers::_hasReturned($a_obj_id, $a_user_id))
				{
					$status = LP_STATUS_IN_PROGRESS_NUM;
				}
				$ex_stat = ilExerciseMembers::_lookupStatus($a_obj_id, $a_user_id);
				if ($ex_stat == "passed")
				{
					$status = LP_STATUS_COMPLETED_NUM;
				}
				if ($ex_stat == "failed")
				{
					$status = LP_STATUS_FAILED_NUM;
				}
				break;			
		}
		return $status;		
	}


}	
?>