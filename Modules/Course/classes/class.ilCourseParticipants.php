<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ModulesCourse 
*/

define("IL_CRS_ADMIN",1);
define("IL_CRS_TUTOR",3);
define("IL_CRS_MEMBER",2);

class ilCourseParticipants
{
	private static $instances = array();

	protected $ilDB = null;
	protected $lng = null;

	protected $course_id = 0;
	protected $course_ref_id = 0;
	protected $course_roles = array();
	protected $course_role_data = array();
	
	protected $participants = array();
	protected $participants_status = array();
	protected $members = array();
	protected $tutors = array();
	protected $admins = array();
	
	protected $subscribers = array();

	/**
	 * Contructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	private function __construct($a_course_id)
	{
	 	global $ilDB,$lng;
	 	
		$this->NOTIFY_DISMISS_SUBSCRIBER = 1;
		$this->NOTIFY_ACCEPT_SUBSCRIBER = 2;
		$this->NOTIFY_DISMISS_MEMBER = 3;
		$this->NOTIFY_BLOCK_MEMBER = 4;
		$this->NOTIFY_UNBLOCK_MEMBER = 5;
		$this->NOTIFY_ACCEPT_USER = 6;
		$this->NOTIFY_ADMINS = 7;
		$this->NOTIFY_STATUS_CHANGED = 8;
		$this->NOTIFY_SUBSCRIPTION_REQUEST = 9;
	 	
	 
	 	$this->ilDB = $ilDB;
	 	$this->lng = $lng;
	 
	 	$this->course_id = $a_course_id;
		$ref_ids = ilObject::_getAllReferences($this->course_id);
		$this->course_ref_id = current($ref_ids);
	 	
	 	
	 	$this->readParticipants();
	 	$this->readParticipantsStatus();
	}
	
	/**
	 * Get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 * @param int course_id
	 */
	public static function _getInstanceByObjId($a_course_id)
	{
		if(isset(self::$instances[$a_course_id]) and self::$instances[$a_course_id])
		{
			return self::$instances[$a_course_id];
		}
		return self::$instances[$a_course_id] = new ilCourseParticipants($a_course_id);
	}
	
	/**
	 * Static function to check if a user is a prticipant of a course
	 *
	 * @access public
	 * @param int course ref_id
	 * @param int user id
	 * @static
	 */
	public function _isParticipant($a_ref_id,$a_usr_id)
	{
		global $rbacreview,$ilObjDataCache,$ilDB,$ilLog;

		$rolf = $rbacreview->getRoleFolderOfObject($a_ref_id);
		if(!isset($rolf['ref_id']) or !$rolf['ref_id'])
		{
			$course_title = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($a_ref_id));
			$ilLog->write(__METHOD__.': Found course without role folder. Course ref_id: '.$a_ref_id.', course title: '.$course_title);
			$ilLog->logStack();
			
			return false;
		}
		$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
		$user_roles = $rbacreview->assignedRoles($a_usr_id);
		
		return count(array_intersect((array) $local_roles,(array) $user_roles)) ? true : false;
	}
	
	/**
	 * Check if user is blocked
	 *
	 * @access public
	 * @static
	 *
	 * @param int course id
	 * @param int usr_id
	 */
	public static function _isBlocked($a_course_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_course_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND blocked = '1'";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false; 
	}
	
	/**
	 * Check if user has passed course
	 *
	 * @access public
	 * @static
	 *
	 * @param int course_id
	 * @param int user id
	 */
	public static function _hasPassed($a_course_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_course_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND passed = '1'";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false; 
	}	
	
	/**
	 * Delete all entries
	 * Normally called for course deletion
	 *
	 * @access public
	 * @static
	 *
	 * @param int course_id
	 */
	public static function _deleteAllEntries($a_course_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_course_id)." ";

		$ilDB->query($query);

		$query = "DELETE FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($a_course_id)."";

		$ilDB->query($query);

		return true;
	}
	
	/**
	 * Delete user data
	 *
	 * @access public
	 * @static
	 *
	 * @param int user id
	 */
	public static function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_members WHERE usr_id = ".$ilDB->quote($a_usr_id)."";
		$ilDB->query($query);

		$query = "DELETE FROM crs_subscribers WHERE usr_id = ".$ilDB->quote($a_usr_id)."";
		$ilDB->query($query);

		include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
		ilCourseWaitingList::_deleteUser($a_usr_id);
	}
	
	/**
	 * Check grouping membership
	 *
	 * @access public
	 * @param 
	 * 
	 */
	public function isGroupingMember($a_usr_id,$a_field = '')
	{
		global $rbacreview,$ilObjDataCache,$ilDB;

		// Used for membership limitations -> check membership by given field
		if($a_field)
		{
			include_once './Services/User/classes/class.ilObjUser.php';

			$tmp_user =& ilObjectFactory::getInstanceByObjId($a_usr_id);
			switch($a_field)
			{
				case 'login':
					$and = "AND login = ".$ilDB->quote($tmp_user->getLogin())." ";
					break;
				case 'email':
					$and = "AND email = ".$ilDB->quote($tmp_user->getEmail())." ";
					break;
				case 'matriculation':
					$and = "AND matriculation = ".$ilDB->quote($tmp_user->getMatriculation())." ";
					break;

				default:
					$and = "AND usr_id = '".$a_usr_id."'";
					break;
			}
			
			if(!$this->getParticipants())
			{
				return false;
			}

			$query = "SELECT * FROM usr_data as ud ".
				"WHERE usr_id IN (".implode(",",ilUtil::quoteArray($this->getParticipants())).") ".
				$and;
			$res = $ilDB->query($query);
			return $res->numRows() ? true : false;
		}
	}
	
	 
	
	/**
	 * check if user is assigned
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isAssigned($a_usr_id)
	{
	 	return in_array($a_usr_id,$this->participants);
	}
	
	/**
	 * Get course roles
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getRoles()
	{
	 	return $this->course_roles ? $this->course_roles : array();
	}
	
	/**
	 * Get assigned roles
	 *
	 * @access public
	 * @param int user_id
	 * 
	 */
	public function getAssignedRoles($a_usr_id)
	{
		global $rbacreview;
		
		foreach($this->course_roles as $role)
		{
			if($rbacreview->isAssigned($a_usr_id,$role))
			{
				$assigned[] = $role;
			}
		}
		return $assigned ? $assigned : array();
	}
	
	/**
	 * Update role assignments
	 *
	 * @access public
	 * @param int usr_id
	 * @param array array of new roles
	 * 
	 */
	public function updateRoleAssignments($a_usr_id,$a_roles)
	{
	 	global $rbacreview,$rbacadmin;
	 	
	 	$roles = $a_roles ? $a_roles : array();
	 	
	 	foreach($this->getRoles() as $role_id)
	 	{
	 		if($rbacreview->isAssigned($a_usr_id,$role_id))
	 		{
	 			if(!in_array($role_id,$roles))
	 			{
	 				$rbacadmin->deassignUser($role_id,$a_usr_id);
	 			}
	 		}
	 		else
	 		{
	 			if(in_array($role_id,$roles))
	 			{
	 				$rbacadmin->assignUser($role_id,$a_usr_id);
	 			}
	 		}
	 	}
	 	$this->readParticipants();
	 	$this->readParticipantsStatus();
	}
	
	/**
	 * Check if user for deletion are last admins
	 *
	 * @access public
	 * @param array array of user ids for deletion
	 * 
	 */
	public function checkLastAdmin($a_usr_ids)
	{
		foreach($this->getAdmins() as $admin_id)
		{
			if(!in_array($admin_id,$a_usr_ids))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Check if user is blocked
	 *
	 * @access public
	 * @param int user_id
	 * 
	 */
	public function isBlocked($a_usr_id)
	{
	 	if(isset($this->participants_status[$a_usr_id]))
	 	{
	 		return $this->participants_status[$a_usr_id]['blocked'] ? true : false; 
	 	}
	 	return false;
	}
	
	/**
	 * Check if user has passed course
	 *
	 * @access public
	 * @param int user_id
	 * 
	 */
	public function hasPassed($a_usr_id)
	{
	 	if(isset($this->participants_status[$a_usr_id]))
	 	{
	 		return $this->participants_status[$a_usr_id]['passed'] ? true : false;
	 	}
	 	return false;
	}
	
	/**
	 * check if notification is enabled
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isNotificationEnabled($a_usr_id)
	{
	 	if(isset($this->participants_status[$a_usr_id]))
	 	{
	 		return $this->participants_status[$a_usr_id]['notification'] ? true : false;
	 	}
	 	return false;
	}
	
	/**
	 * Get admin, tutor which have notification enabled
	 *
	 * @access public
	 * @return array array of user ids
	 */
	public function getNotificationRecipients()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM crs_members ".
	 		"WHERE notification = 1 ".
	 		"AND obj_id = ".$ilDB->quote($this->course_id)." ";
	 	$res = $ilDB->query($query);
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		if($this->isAdmin($row->usr_id) or $this->isTutor($row->usr_id))
	 		{
	 			$recp[] = $row->usr_id;
	 		}
	 	}
	 	return $recp ? $recp : array();
	}
	
	/**
	 * Get number of members (not participants)
	 *
	 * @access public
	 * 
	 */
	public function getCountMembers()
	{
	 	return count($this->members);
	}
	
	/**
	 * Get number of participants
	 *
	 * @access public
	 * 
	 */
	public function getCountParticipants()
	{
	 	return count($this->participants);
	}
	
	
	
	
	/**
	 * Get all participants ids
	 *
	 * @access public
	 * @return array array of user ids
	 */
	public function getParticipants()
	{
	 	return $this->participants ? $this->participants : array();
	}
	
	/**
	 * Get all members ids (admins and tutors are not members)
	 * Use get participants to fetch all
	 *
	 * @access public
	 * @return array array of user ids
	 */
	public function getMembers()
	{
	 	return $this->members ? $this->members : array();
	}
	/**
	 * Get all admins ids
	 *
	 * @access public
	 * @return array array of user ids
	 */
	public function getAdmins()
	{
	 	return $this->admins ? $this->admins : array();
	}
	/**
	 * Get all tutors ids
	 *
	 * @access public
	 * @return array array of user ids
	 */
	public function getTutors()
	{
	 	return $this->tutors ? $this->tutors : array();
	}
	
	/**
	 * is user admin
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	public function isAdmin($a_usr_id)
	{
		return in_array($a_usr_id,$this->admins) ? true : false;	
	}
	
	/**
	 * is user tutor
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	public function isTutor($a_usr_id)
	{
		return in_array($a_usr_id,$this->tutors) ? true : false;	
	}
	
	/**
	 * is user member
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	public function isMember($a_usr_id)
	{
		return in_array($a_usr_id,$this->members) ? true : false;	
	}
	
	/**
	 * Update passed status
	 *
	 * @access public
	 * @param int usr_id
	 * @param bool passed
	 * 
	 */
	public function updatePassed($a_usr_id,$a_passed)
	{
		global $ilDB;
		
		$this->participants_status[$a_usr_id]['passed'] = (int) $a_passed;

		$query = "SELECT * FROM crs_members ".
		"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id);
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE crs_members SET ".
				"passed = ".$ilDB->quote((int) $a_passed)." ".
				"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id);
		}
		else
		{
			$query = "INSERT INTO crs_members SET ".
				"passed = ".$ilDB->quote((int) $a_passed).", ".
				"obj_id = ".$ilDB->quote($this->course_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id);
			
		}
		$res = $ilDB->query($query);
		return true;
	
	}
	
	/**
	 * Update notification status
	 *
	 * @access public
	 * @param int usr_id
	 * @param bool passed
	 * 
	 */
	public function updateNotification($a_usr_id,$a_notification)
	{
		global $ilDB;
		
		$this->participants_status[$a_usr_id]['notification'] = (int) $a_notification;

		$query = "SELECT * FROM crs_members ".
		"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id);
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE crs_members SET ".
				"notification = ".$ilDB->quote((int) $a_notification)." ".
				"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id);
		}
		else
		{
			$query = "INSERT INTO crs_members SET ".
				"notification = ".$ilDB->quote((int) $a_notification).", ".
				"obj_id = ".$ilDB->quote($this->course_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id);
			
		}
		$res = $ilDB->query($query);
		return true;
	
	}

	/**
	 * Update blocked status
	 *
	 * @access public
	 * @param int usr_id
	 * @param bool blocked
	 * 
	 */
	public function updateBlocked($a_usr_id,$a_blocked)
	{
		global $ilDB;
		
		$this->participants_status[$a_usr_id]['blocked'] = (int) $a_blocked;

		$query = "SELECT * FROM crs_members ".
		"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id);
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE crs_members SET ".
				"blocked = ".$ilDB->quote((int) $a_blocked)." ".
				"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id);
		}
		else
		{
			$query = "INSERT INTO crs_members SET ".
				"blocked = ".$ilDB->quote((int) $a_blocked).", ".
				"obj_id = ".$ilDB->quote($this->course_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id);
			
		}
		$res = $ilDB->query($query);
		return true;
	}

	/**
	 * Add user to course
	 *
	 * @access public
	 * @param int user id
	 * @param int role IL_CRS_ADMIN || IL_CRS_TUTOR || IL_CRS_MEMBER
	 * 
	 */
	public function add($a_usr_id,$a_role)
	{
	 	global $rbacadmin;
	 	
	 	if($this->isAssigned($a_usr_id))
	 	{
	 		return false;
	 	}
	 	
	 	switch($a_role)
	 	{
	 		case IL_CRS_ADMIN:
	 			$this->admins[] = $a_usr_id;
	 			break;
	 		case IL_CRS_TUTOR:
	 			$this->tutors[] = $a_usr_id;
	 			break;
	 		case IL_CRS_MEMBER:
	 			$this->members[] = $a_usr_id;
	 			break;
	 	}
		$this->participants[] = $a_usr_id;
		$rbacadmin->assignUser($this->course_role_data[$a_role],$a_usr_id);
		$this->addDesktopItem($a_usr_id);
	 	return true;
	}
	
	/**
	 * Drop user from all course roles
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	public function delete($a_usr_id)
	{
		global $rbacadmin,$ilDB;
		
		$this->dropDesktopItem($a_usr_id);
		foreach($this->course_roles as $role_id)
		{
			$rbacadmin->deassignUser($role_id,$a_usr_id);
		}
		
		$query = "DELETE FROM crs_members ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->course_id);
		$ilDB->query($query);
		
		$this->readParticipants();
		$this->readParticipantsStatus();
		
		return true;
	}

	/**
	 * Delete users
	 *
	 * @access public
	 * @param array user ids
	 * 
	 */
	public function deleteParticipants($a_user_ids)
	{
	 	foreach($a_user_ids as $user_id)
	 	{
	 		$this->delete($user_id);
	 	}
	 	return true;
	}
	
	
	
	/**
	 * Add desktop item
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	public function addDesktopItem($a_usr_id)
	{
		if(!ilObjUser::_isDesktopItem($a_usr_id, $this->course_ref_id,'crs'))
		{
			ilObjUser::_addDesktopItem($a_usr_id, $this->course_ref_id,'crs');
		}
		return true;
	}
	
	
	/**
	 * Drop desktop item
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	function dropDesktopItem($a_usr_id)
	{
		if(ilObjUser::_isDesktopItem($a_usr_id, $this->course_ref_id,'crs'))
		{
			ilObjUser::_dropDesktopItem($a_usr_id, $this->course_ref_id,'crs');
		}

		return true;
	}
	
	
	

	/**
	 * Read participants
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function readParticipants()
	{
		global $rbacreview,$ilObjDataCache,$ilLog;

		$rolf = $rbacreview->getRoleFolderOfObject($this->course_ref_id);
		if(!isset($rolf['ref_id']) or !$rolf['ref_id'])
		{
			$course_title = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->course_ref_id));
			$ilLog->write(__METHOD__.': Found course without role folder. Course ref_id: '.$this->course_ref_id.', course title: '.$course_title);
			$ilLog->logStack();
			return false;
		}
		
		$this->course_roles = $rbacreview->getRolesOfRoleFolder($rolf['ref_id'],false);

		$users = array();
		$this->participants = array();
		$this->members = $this->admins = $this->tutors = array();
		foreach($this->course_roles as $role_id)
		{
			$title = $ilObjDataCache->lookupTitle($role_id);
			switch(substr($title,0,8))
			{
				case 'il_crs_m':
					$this->course_role_data[IL_CRS_MEMBER] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->members = array_unique(array_merge($assigned,$this->members));
					break;

				case 'il_crs_a':
					$this->course_role_data[IL_CRS_ADMIN] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->admins = $rbacreview->assignedUsers($role_id);
					break;
		
				case 'il_crs_t':
					$this->course_role_data[IL_CRS_TUTOR] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->tutors = $rbacreview->assignedUsers($role_id);
					break;
				
				default:
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->members = array_unique(array_merge($assigned,$this->members));
					break;
			}
		}
	}
	
	/**
	 * Read stati of participants (blocked, notification, passed)
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function readParticipantsStatus()
	{
	 	global $ilDB;
	 	
	 	$query = "SELECT * FROM crs_members ".
	 		"WHERE obj_id = ".$ilDB->quote($this->course_id)." ";
	 	$res = $ilDB->query($query);
	 	$this->participants_status = array();
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->participants_status[$row->usr_id]['blocked'] = $row->blocked;
	 		$this->participants_status[$row->usr_id]['notification']  = $row->notification;
	 		$this->participants_status[$row->usr_id]['passed'] = $row->passed;
	 	}
	}
	
	
		// METHODS FOR NEW REGISTRATIONS
	function getSubscribers()
	{
		$this->__readSubscribers();

		return $this->subscribers;
	}

	function getCountSubscribers()
	{
		return count($this->getSubscribers());
	}

	function getSubscriberData($a_usr_id)
	{
		return $this->__readSubscriberData($a_usr_id);
	}



	function assignSubscribers($a_usr_ids)
	{
		if(!is_array($a_usr_ids) or !count($a_usr_ids))
		{
			return false;
		}
		foreach($a_usr_ids as $id)
		{
			if(!$this->assignSubscriber($id))
			{
				return false;
			}
		}
		return true;
	}

	function assignSubscriber($a_usr_id)
	{
		global $ilErr;
		
		$ilErr->setMessage("");
		if(!$this->isSubscriber($a_usr_id))
		{
			$ilErr->appendMessage($this->lng->txt("crs_user_notsubscribed"));

			return false;
		}
		if($this->isAssigned($a_usr_id))
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($a_usr_id);
			$ilErr->appendMessage($tmp_obj->getLogin().": ".$this->lng->txt("crs_user_already_assigned"));

			return false;
		}

		if(!$tmp_obj =& ilObjectFactory::getInstanceByObjId($a_usr_id))
		{
			$ilErr->appendMessage($this->lng->txt("crs_user_not_exists"));

			return false;
		}

		$this->add($tmp_obj->getId(),IL_CRS_MEMBER);
		$this->deleteSubscriber($a_usr_id);

		return true;
	}

	function autoFillSubscribers()
	{
		$this->__readSubscribers();

		$counter = 0;
		foreach($this->subscribers as $subscriber)
		{
			if(!$this->assignSubscriber($subscriber))
			{
				continue;
			}
			else
			{
				$this->sendNotification($this->NOTIFY_ACCEPT_SUBSCRIBER,$subscriber);
			}
			++$counter;
		}

		return $counter;
	}

	function addSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "INSERT INTO crs_subscribers ".
			" VALUES (".$ilDB->quote($a_usr_id).",".$ilDB->quote($this->course_id).",".$ilDB->quote(time()).")";
		$res = $this->ilDB->query($query);

		return true;
	}

	function updateSubscriptionTime($a_usr_id,$a_subtime)
	{
		global $ilDB;

		$query = "UPDATE crs_subscribers ".
			"SET sub_time = ".$ilDB->quote($a_subtime)." ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->course_id)." ";

		$this->db->query($query);

		return true;
	}

	function deleteSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_subscribers ".
			"WHERE usr_id = ".$a_usr_id." ".
			"AND obj_id = ".$ilDB->quote($this->course_id)." ";

		$res = $ilDB->query($query);

		return true;
	}

	function deleteSubscribers($a_usr_ids)
	{
		global $ilErr;
		
		if(!is_array($a_usr_ids) or !count($a_usr_ids))
		{
			$ilErr->setMessage('');
			$ilErr->appendMessage($this->lng->txt("no_usr_ids_given"));

			return false;
		}
		foreach($a_usr_ids as $id)
		{
			if(!$this->deleteSubscriber($id))
			{
				$ilErr->appendMessage($this->lng->txt("error_delete_subscriber"));

				return false;
			}
		}
		return true;
	}
	function isSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->course_id)."";

		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}

	/*
	 * Static method
	 */
	function _isSubscriber($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)."";

		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}
	function __readSubscribers()
	{
		global $ilDB;

		$this->subscribers = array();

		$query = "SELECT usr_id FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
			"ORDER BY sub_time ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// DELETE SUBSCRIPTION IF USER HAS BEEN DELETED
			if(!ilObjectFactory::getInstanceByObjId($row->usr_id,false))
			{
				$this->deleteSubscriber($row->usr_id);
			}
			$this->subscribers[] = $row->usr_id;
		}
		return true;
	}

	function __readSubscriberData($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->course_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data["time"] = $row->sub_time;
			$data["usr_id"] = $row->usr_id;
		}
		return $data ? $data : array();
	}
	
	
	// Subscription
	function sendNotification($a_type, $a_usr_id)
	{
		global $ilObjDataCache,$ilUser;
	
		$tmp_user =& ilObjectFactory::getInstanceByObjId($a_usr_id,false);

		$link = ("\n\n".$this->lng->txt('crs_mail_permanent_link'));
		$link .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);

		switch($a_type)
		{
			case $this->NOTIFY_DISMISS_SUBSCRIBER:
				$subject = $this->lng->txt("crs_reject_subscriber");
				$body = $this->lng->txt("crs_reject_subscriber_body");
				break;

			case $this->NOTIFY_ACCEPT_SUBSCRIBER:
				$subject = $this->lng->txt("crs_accept_subscriber");
				$body = $this->lng->txt("crs_accept_subscriber_body");
				$body .= $link;
				break;
			case $this->NOTIFY_DISMISS_MEMBER:
				$subject = $this->lng->txt("crs_dismiss_member");
				$body = $this->lng->txt("crs_dismiss_member_body");
				break;
			case $this->NOTIFY_BLOCK_MEMBER:
				$subject = $this->lng->txt("crs_blocked_member");
				$body = $this->lng->txt("crs_blocked_member_body");
				break;
			case $this->NOTIFY_UNBLOCK_MEMBER:
				$subject = $this->lng->txt("crs_unblocked_member");
				$body = $this->lng->txt("crs_unblocked_member_body");
				$body .= $link;
				break;
			case $this->NOTIFY_ACCEPT_USER:
				$subject = $this->lng->txt("crs_added_member");
				$body = $this->lng->txt("crs_added_member_body");
				$body .= $link;
				break;
			case $this->NOTIFY_STATUS_CHANGED:
				$subject = $this->lng->txt("crs_status_changed");
				$body = $this->__buildStatusBody($tmp_user);
				$body .= $link;
				break;

			case $this->NOTIFY_SUBSCRIPTION_REQUEST:
				$this->sendSubscriptionRequestToAdmins($a_usr_id);
				return true;
				break;

			case $this->NOTIFY_ADMINS:
				$this->sendNotificationToAdmins($a_usr_id);

				return true;
				break;
		}
		$subject = sprintf($subject,$ilObjDataCache->lookupTitle($this->course_id));
		$body = sprintf($body,$ilObjDataCache->lookupTitle($this->course_id));

		include_once("Services/Mail/classes/class.ilMail.php");
		$mail = new ilMail($ilUser->getId());
		$mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));

		unset($tmp_user);
		return true;
	}
	
	function sendUnsubscribeNotificationToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;

		if(!ilObjCourse::_isSubscriptionNotificationEnabled($this->course_id))
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_cancel_subscription"),$ilObjDataCache->lookupTitle($this->course_id));
		$body = sprintf($this->lng->txt("crs_cancel_subscription_body"),$ilObjDataCache->lookupTitle($this->course_id));
		$body .= ("\n\n".$this->lng->txt('crs_mail_permanent_link'));
		$body .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);
		

		foreach($this->getNotificationRecipients() as $usr_id)
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id,false);
			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('normal'));
			unset($tmp_user);
		}
		return true;
	}
	
	
	function sendSubscriptionRequestToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache,$ilUser;

		if(!ilObjCourse::_isSubscriptionNotificationEnabled($this->course_id))
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilMail.php");

		$mail = new ilMail($ilUser->getId());
		$subject = sprintf($this->lng->txt("crs_new_subscription_request"),$ilObjDataCache->lookupTitle($this->course_id));
		$body = sprintf($this->lng->txt("crs_new_subscription_request_body"),$ilObjDataCache->lookupTitle($this->course_id));
		$body .= ("\n\n".$this->lng->txt('crs_new_subscription_request_body2'));
		$body .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);

		foreach($this->getNotificationRecipients() as $usr_id)
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id,false);
			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));
		}
		return true;
	}
	

	function sendNotificationToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;

		if(!ilObjCourse::_isSubscriptionNotificationEnabled($this->course_id))
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_new_subscription"),$ilObjDataCache->lookupTitle($this->course_id));
		$body = sprintf($this->lng->txt("crs_new_subscription_body"),$ilObjDataCache->lookupTitle($this->course_id));

		$query = "SELECT usr_id FROM crs_members ".
			"WHERE notification = '1' ".
			"AND obj_id = ".$ilDB->quote($this->course_id)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($this->isAdmin($row->usr_id) or $this->isTutor($row->usr_id))
			{
				$tmp_user =& ilObjectFactory::getInstanceByObjId($row->usr_id,false);
				$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));
				unset($tmp_user);
			}
		}
		unset($mail);

		return true;
	}
	
	function __buildStatusBody(&$user_obj)
	{
		global $ilDB;

		$body = $this->lng->txt('crs_status_changed_body').':<br />';
		$body .= $this->lng->txt('login').': '.$user_obj->getLogin().'<br />';
		$body .= $this->lng->txt('role').': ';

		if($this->isAdmin($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_member').'<br />';
		}
		if($this->isTutor($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_tutor').'<br />';
		}
		if($this->isMember($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_member').'<br />';
		}
		$body .= $this->lng->txt('status').': ';
		
		if($this->isNotificationEnabled($user_obj->getId()))
		{
			$body .= $this->lng->txt("crs_notify").'<br />';
		}
		else
		{
			$body .= $this->lng->txt("crs_no_notify").'<br />';
		}
		if($this->isBlocked($user_obj->getId()))
		{
			$body .= $this->lng->txt("crs_blocked").'<br />';
		}
		else
		{
			$body .= $this->lng->txt("crs_unblocked").'<br />';
		}
		$passed = $this->hasPassed($user_obj->getId()) ? $this->lng->txt('yes') : $this->lng->txt('no');
		$body .= $this->lng->txt('crs_passed').': '.$passed.'<br />';

		return $body;
	}
	
	
}
?>