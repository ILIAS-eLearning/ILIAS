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
* @author Stefan Meyer <alex.killing@gmx.de>
*
* @version $Id$
*
*/

include_once './Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusSCORMPackage extends ilLPStatus
{

	function ilLPStatusSCORMPackage($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}

	function _getInProgress($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = $status_info['in_progress'];
		return array_unique($users);
	}

	function _getCompleted($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = $status_info['completed'];
		return array_unique($users);
	}

	function _getFailed($a_obj_id)
	{
		$status_info = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$users = $status_info['failed'];
		return array_unique($users);
	}

	
	function _getStatusInfo($a_obj_id)
	{
		include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
		$status_info['subtype'] = "scorm2004";
		$info = ilSCORM2004Tracking::_getProgressInfo($a_obj_id);

		$status_info['completed'] = $info['completed'];
		$status_info['failed'] = $info['failed'];
		$status_info['in_progress'] = $info['in_progress'];

		return $status_info;
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
		global $ilObjDataCache, $ilDB, $ilLog;
		
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
		$scorm_status = ilSCORM2004Tracking::_getProgressInfoOfUser($a_obj_id, $a_user_id);
		$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
		switch ($scorm_status)
		{
			case "in_progress":
				$status = self::LP_STATUS_IN_PROGRESS_NUM;
				break;
			case "completed":
				$status = self::LP_STATUS_COMPLETED_NUM;
				break;
			case "failed":
				$status = self::LP_STATUS_FAILED_NUM;
				break;
		}

		return $status;		
	}

	function refreshStatus($a_obj_id)
	{
		parent::refreshStatus($a_obj_id);
		
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");	
		$in_progress = ilLPStatusWrapper::_getInProgress($a_obj_id);		
		$completed = ilLPStatusWrapper::_getCompleted($a_obj_id);		
		$failed = ilLPStatusWrapper::_getFailed($a_obj_id);	
		$all_active_users = array_unique(array_merge($in_progress, $completed, $failed));
		
		// get all tracked users regardless of SCOs
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
		$all_tracked_users = ilSCORM2004Tracking::_getTrackedUsers($a_obj_id);			
		
		$not_attempted_users = array_diff($all_tracked_users, $all_active_users);
		unset($all_tracked_users);
		unset($all_active_users);
		
		// reset all users which have no data for the current SCOs
		if($not_attempted_users)
		{
			foreach($not_attempted_users as $usr_id)
			{
				// this will update any (parent) collections if necessary
				ilLPStatus::writeStatus($a_obj_id, $usr_id, self::LP_STATUS_NOT_ATTEMPTED_NUM, 0);				
			}						
		}
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
		return null;//todo!
	}

}	
?>