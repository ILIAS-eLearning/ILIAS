<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilLPStatus.php';


/**
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @package ilias-tracking
 *
 */
class ilLPStatusObjectives extends ilLPStatus
{

	function ilLPStatusObjectives($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db =& $ilDB;
	}
	
	function _getNotAttempted($a_obj_id)
	{
		$users = array();
		
		$members = self::getMembers($a_obj_id);
		if($members)
		{
			// diff in progress, completed and failed (use stored result in LPStatusWrapper)
			$users = array_diff((array) $members, ilLPStatusWrapper::_getInProgress($a_obj_id));
			$users = array_diff((array) $members, ilLPStatusWrapper::_getCompleted($a_obj_id));
			$users = array_diff((array) $members, ilLPStatusWrapper::_getFailed($a_obj_id));
		}

		return $users;
	}

	function _getInProgress($a_obj_id)
	{		
		$objective_results = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$usr_ids = (array)$objective_results['user_status'][self::LP_STATUS_IN_PROGRESS_NUM];
		
		/* change_event is of no use when no objective has been tried yet
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		
		// Exclude all users with status completed/failed.
		$users = array_diff((array) $users,ilLPStatusWrapper::_getCompleted($a_obj_id));
		$users = array_diff((array) $users,ilLPStatusWrapper::_getFailed($a_obj_id));
		*/
		
		if($usr_ids)
		{
			// Exclude all non members
			$usr_ids = array_intersect(self::getMembers($a_obj_id), (array)$usr_ids);
		}
		
		return $usr_ids ? $usr_ids : array();
	}

	function _getCompleted($a_obj_id)
	{						
		$objective_results = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$usr_ids = (array)$objective_results['user_status'][self::LP_STATUS_COMPLETED_NUM];
				
		if($usr_ids)
		{
			// Exclude all non members
			$usr_ids = array_intersect(self::getMembers($a_obj_id), (array)$usr_ids);
		}
		
		return $usr_ids ? $usr_ids : array();
	}
	
	function _getFailed($a_obj_id)
	{	
		$objective_results = ilLPStatusWrapper::_getStatusInfo($a_obj_id);
		$usr_ids = (array)$objective_results['user_status'][self::LP_STATUS_FAILED_NUM];		
		
		if($usr_ids)
		{
			// Exclude all non members
			$usr_ids = array_intersect(self::getMembers($a_obj_id), (array)$usr_ids);
		}
		
		return $usr_ids ? $usr_ids : array();		
	}		
	
	function _getStatusInfo($a_obj_id)
	{
		global $ilDB;

		include_once 'Modules/Course/classes/class.ilCourseObjective.php';

		$status_info = array();
		$status_info['user_status'] = array();
		$status_info['objectives'] = ilCourseObjective::_getObjectiveIds($a_obj_id,true);
		$status_info['num_objectives'] = count($status_info['objectives']);

		if($status_info['num_objectives'])
		{			
			$in = $ilDB->in('objective_id',$status_info['objectives'],false,'integer');
						
			include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
			foreach(ilLOUserResults::getSummarizedObjectiveStatusForLP($status_info['objectives']) as $user_id => $user_status)
			{
				$status_info['user_status'][$user_status][] = $user_id;							
			}						
			
			// Read title/description
			$query = "SELECT * FROM crs_objectives WHERE ".$in;
			$res = $ilDB->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$status_info['objective_title'][$row->objective_id] = $row->title;
				$status_info['objective_description'][$row->objective_id] = $row->description;
			}
		}
		
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
		global $ilObjDataCache, $ilDB;
	
		// the status completed depends on:
		// $status_info['num_objectives'] (ilLPStatusWrapper::_getStatusInfo($a_obj_id);)
		// - ilCourseObjective::_getObjectiveIds($a_obj_id);
		// - table crs_objectives manipulated in
		// - ilCourseObjective
		
		// $status_info['objective_result']  (ilLPStatusWrapper::_getStatusInfo($a_obj_id);)
		// table crs_objective_status (must not contain a dataset)
		// ilCourseObjectiveResult -> added ilLPStatusWrapper::_updateStatus()
	
		$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
		switch ($ilObjDataCache->lookupType($a_obj_id))
		{
			case "crs":
				include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
				if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id))
				{
					// change_event is of no use when no objective has been tried
					// $status = self::LP_STATUS_IN_PROGRESS_NUM;

					include_once 'Modules/Course/classes/class.ilCourseObjective.php';				
					$objectives = ilCourseObjective::_getObjectiveIds($a_obj_id, true);					
					if ($objectives)
					{												
						// #14051 - getSummarizedObjectiveStatusForLP() might return null
						include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";						
						$status = (int)ilLOUserResults::getSummarizedObjectiveStatusForLP($objectives, $a_user_id);													
					}					
				}
				break;			
		}
		return $status;
	}
	
	/**
	 * Get members for object
	 * @param int $a_obj_id
	 * @return array
	 */
	protected static function getMembers($a_obj_id)
	{				
		include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
		$member_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
		return $member_obj->getMembers();						
	}
	
	/**
	 * Get completed users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupCompletedForObject($a_obj_id, $a_user_ids = null)
	{
		if(!$a_user_ids)
		{
			$a_user_ids = self::getMembers($a_obj_id);
			if(!$a_user_ids)
			{
				return array();
			}
		}
		return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_COMPLETED_NUM, $a_user_ids);
	}
	
	/**
	 * Get failed users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupFailedForObject($a_obj_id, $a_user_ids = null)
	{
		return array();
	}
	
	/**
	 * Get in progress users for object
	 * 
	 * @param int $a_obj_id
	 * @param array $a_user_ids
	 * @return array 
	 */
	public static function _lookupInProgressForObject($a_obj_id, $a_user_ids = null)
	{
		if(!$a_user_ids)
		{
			$a_user_ids = self::getMembers($a_obj_id);
			if(!$a_user_ids)
			{
				return array();
			}
		}
		return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_IN_PROGRESS_NUM, $a_user_ids);
	}	
}
		

?>