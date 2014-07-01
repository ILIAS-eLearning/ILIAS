<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* @version $Id$
* 
* 
* @ilCtrl_Calls 
* @ingroup ServicesTracking 
*/

include_once 'Services/Tracking/classes/class.ilLPStatus.php';

class ilLPStatusManualByTutor extends ilLPStatus
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param int object id
	 * 
	 */
	function __construct($a_obj_id)
	{
		global $ilDB;

		parent::ilLPStatus($a_obj_id);
		$this->db = $ilDB;
	}
	
	/**
	 * get not attempted
	 *
	 * @access public
	 * @param int object id
	 * @return array int Array of user ids
	 * 
	 */
	public function _getNotAttempted($a_obj_id)
	{		
		$users = array();
	
		$members = self::getMembers($a_obj_id);
		if($members)
		{
			// diff in progress and completed (use stored result in LPStatusWrapper)
			$users = array_diff($members, ilLPStatusWrapper::_getInProgress($a_obj_id));
			$users = array_diff($users, ilLPStatusWrapper::_getCompleted($a_obj_id));		
			
			// patch generali start
			$users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));			
			// patch generali end
		}
		
		return $users;
	}
	
	/**
	 * get in progress
	 *
	 * @access public
	 * @param int object id
	 * @return array int Array of user ids
	 */
	public function _getInProgress($a_obj_id)
	{		
		include_once './Services/Tracking/classes/class.ilChangeEvent.php';
		$users = ilChangeEvent::lookupUsersInProgress($a_obj_id);
		
		// Exclude all users with status completed.
		$users = array_diff((array) $users,ilLPStatusWrapper::_getCompleted($a_obj_id));
		
		// patch generali start
		$users = array_diff($users, ilLPStatusWrapper::_getFailed($a_obj_id));
		// patch generali end

		if($users)
		{
			// Exclude all non members
			$users = array_intersect(self::getMembers($a_obj_id), (array)$users);
		}
		
		return $users;
	}
	
	function _getCompleted($a_obj_id)
	{
		global $ilDB;
		
		$usr_ids = array();

		$query = "SELECT DISTINCT(usr_id) user_id FROM ut_lp_marks ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND completed = '1' ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->user_id;
		}
		
		if($usr_ids)
		{
			// Exclude all non members
			$usr_ids = array_intersect(self::getMembers($a_obj_id), (array)$usr_ids);
		}

		return $usr_ids;
	}
	
	// patch generali start	
	function _getFailed($a_obj_id)
	{
		global $ilDB;
		
		$usr_ids = array();

		$query = "SELECT DISTINCT(usr_id) user_id FROM ut_lp_marks ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND status = ".$ilDB->quote(ilLPStatus::LP_STATUS_FAILED_NUM, 'integer');
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->user_id;
		}
		
		if($usr_ids)
		{
			// Exclude all non members
			$usr_ids = array_intersect(self::getMembers($a_obj_id), (array)$usr_ids);
		}

		return $usr_ids;
	}
	// patch generali end	
	
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
		
		$status = self::LP_STATUS_NOT_ATTEMPTED_NUM;
		switch ($ilObjDataCache->lookupType($a_obj_id))
		{
			case "crs":
			case "grp":
				
				// patch generali start
				
				$set = $ilDB->query("SELECT usr_id,u_comment,completed".
					" FROM ut_lp_marks".
					" WHERE obj_id = ".$ilDB->quote($a_obj_id, 'integer').
					" AND usr_id = ".$ilDB->quote($a_user_id, 'integer'));
				$row = $ilDB->fetchAssoc($set);				
				if (is_array($row))
				{
					if(substr($row["u_comment"], 0, 10) == "lp_status_")
					{
						$status = (int)substr($row["u_comment"], 10);						
					}					
					else if($row["completed"])
					{
						$status = self::LP_STATUS_COMPLETED_NUM;
					}
				}
				else
				{				
					include_once './Services/Tracking/classes/class.ilChangeEvent.php';
					if (ilChangeEvent::hasAccessed($a_obj_id, $a_user_id))
					{
						$status = self::LP_STATUS_IN_PROGRESS_NUM;
					}
				}
				
				// patch generali end
				
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
		global $ilObjDataCache;
	
		switch($ilObjDataCache->lookupType($a_obj_id))
		{
			case 'crs':
				include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
				$member_obj = ilCourseParticipants::_getInstanceByObjId($a_obj_id);
				return $member_obj->getMembers();
				
			case 'grp':
				include_once './Modules/Group/classes/class.ilObjGroup.php';
				return ilObjGroup::_getMembers($a_obj_id);			
		}
		
		return array();
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
		// patch generali start
		
		if(!$a_user_ids)
		{
			$a_user_ids = self::getMembers($a_obj_id);
			if(!$a_user_ids)
			{
				return array();
			}
		}
		return self::_lookupStatusForObject($a_obj_id, self::LP_STATUS_FAILED_NUM, $a_user_ids);
		
		// patch generali end
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