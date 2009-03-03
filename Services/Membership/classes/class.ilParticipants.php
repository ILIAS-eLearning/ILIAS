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
* Base class for course and group participants
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership 
*/

define("IL_CRS_ADMIN",1);
define("IL_CRS_TUTOR",3);
define("IL_CRS_MEMBER",2);

define('IL_GRP_ADMIN',4);
define('IL_GRP_MEMBER',5);


class ilParticipants
{
	protected $obj_id = 0;
	protected $type = '';
	protected $ref_id = 0;
	
	protected $roles = array();
	protected $role_data = array();
	
	protected $participants = array();
	protected $participants_status = array();
	protected $members = array();
	protected $tutors = array();
	protected $admins = array();
	
	protected $subscribers = array();
	
	protected $ilDB;
	protected $lng;
	

	/**
	 * Singleton Constructor
	 *
	 * @access public
	 * @param int obj_id of container
	 * 
	 */
	protected function __construct($a_obj_id)
	{
	 	global $ilDB,$lng;
	 	
	 	$this->ilDB = $ilDB;
	 	$this->lng = $lng;
	 
	 	$this->obj_id = $a_obj_id;
	 	$this->type = ilObject::_lookupType($a_obj_id);
		$ref_ids = ilObject::_getAllReferences($this->obj_id);
		$this->ref_id = current($ref_ids);
	 	
	 	
	 	$this->readParticipants();
	 	$this->readParticipantsStatus();
	}
	
	/**
	 * get membership by type
	 * Get course or group membership
	 *
	 * @access public
	 * @param int $a_usr_id usr_id
	 * @param string $a_type crs or grp
	 * @return
	 * @static
	 */
	public static function _getMembershipByType($a_usr_id,$a_type)
	{
		global $ilDB;
		
		$query = "SELECT DISTINCT obd.obj_id,obr.ref_id FROM rbac_ua AS ua ".
			"JOIN rbac_fa fa ON ua.rol_id = fa.rol_id ".
			"JOIN tree t1 ON t1.child = fa.parent ".
			"JOIN object_reference obr ON t1.parent = obr.ref_id ".
			"JOIN object_data obd ON obr.obj_id = obd.obj_id ".
			"WHERE obd.type = ".$ilDB->quote($a_type,'text')." ".
			"AND fa.assign = 'y' ".
			"AND ua.usr_id = ".$ilDB->quote($a_usr_id,'integer')." ";
		$res = $ilDB->query($query);
		
		while($row = $ilDB->fetchObject($res))
		{
			$ref_ids[] = $row->obj_id;
		}
		
		return $ref_ids ? $ref_ids : array();			
	}
	
	
	
	/**
	 * Static function to check if a user is a participant of the container object
	 *
	 * @access public
	 * @param int ref_id
	 * @param int user id
	 * @static
	 */
	public static function _isParticipant($a_ref_id,$a_usr_id)
	{
		global $rbacreview,$ilObjDataCache,$ilDB,$ilLog;

		$rolf = $rbacreview->getRoleFolderOfObject($a_ref_id);
		if(!isset($rolf['ref_id']) or !$rolf['ref_id'])
		{
			$title = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($a_ref_id));
			$ilLog->write(__METHOD__.': Found object without role folder. Ref_id: '.$a_ref_id.', title: '.$title);
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
	public static function _isBlocked($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id,'integer')." ".
			"AND blocked = ".$ilDB->quote(1,'integer');
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false; 
	}
	
	/**
	 * Check if user has passed course
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 * @param int user id
	 */
	public static function _hasPassed($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
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
	 * @param int obj_id
	 */
	public static function _deleteAllEntries($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		$query = "DELETE FROM il_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')."";
		$res = $ilDB->manipulate($query);

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

		$query = "DELETE FROM crs_members WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')."";
		$res = $ilDB->manipulate($query);

		$query = "DELETE FROM il_subscribers WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')."";
		$res = $ilDB->manipulate($query);

		include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
		ilCourseWaitingList::_deleteUser($a_usr_id);
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
	 		"AND obj_id = ".$ilDB->quote($this->obj_id)." ";
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
	 	return $this->roles ? $this->roles : array();
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
		
		foreach($this->roles as $role)
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
	 * Drop user from all roles
	 *
	 * @access public
	 * @param int usr_id
	 * 
	 */
	public function delete($a_usr_id)
	{
		global $rbacadmin,$ilDB;
		
		$this->dropDesktopItem($a_usr_id);
		foreach($this->roles as $role_id)
		{
			$rbacadmin->deassignUser($role_id,$a_usr_id);
		}
		
		$query = "DELETE FROM crs_members ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer');
		$res = $ilDB->manipulate($query);
		
		$this->readParticipants();
		$this->readParticipantsStatus();
		
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
		"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE crs_members SET ".
				"blocked = ".$ilDB->quote((int) $a_blocked ,'integer')." ".
				"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		}
		else
		{
			$query = "INSERT INTO crs_members (blocked,obj_id,usr_id,notification,passed) ".
				"VALUES ( ".
				$ilDB->quote((int) $a_blocked ,'integer').", ".
				$ilDB->quote($this->obj_id ,'integer').", ".
				$ilDB->quote($a_usr_id ,'integer').", ".
				$ilDB->quote(0,'integer').", ".
				$ilDB->quote(0,'integer').
				")";
			
		}
		$res = $ilDB->manipulate($query);
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
		"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE crs_members SET ".
				"notification = ".$ilDB->quote((int) $a_notification ,'integer')." ".
				"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		}
		else
		{
			$query = "INSERT INTO crs_members (notification,obj_id,usr_id,passed,blocked) ".
				"VALUES ( ".
				$ilDB->quote((int) $a_notification ,'integer').", ".
				$ilDB->quote($this->obj_id ,'integer').", ".
				$ilDB->quote($a_usr_id ,'integer').", ".
				$ilDB->quote(0,'integer').", ".
				$ilDB->quote(0,'integer').
				")";
			
		}
		$res = $ilDB->manipulate($query);
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
	 	global $rbacadmin,$ilLog,$ilAppEventHandler;
	 	
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
	 			
	 		case IL_GRP_ADMIN:
	 			$this->admins[] = $a_usr_id;
	 			break;
	 			
	 		case IL_GRP_MEMBER:
	 			$this->members[] = $a_usr_id;
	 			break;
	 	}
		$this->participants[] = $a_usr_id;
		$rbacadmin->assignUser($this->role_data[$a_role],$a_usr_id);
		$this->addDesktopItem($a_usr_id);

		if($this->type == 'crs') {
		 	// Add event: used for ecs accounts
			$ilLog->write(__METHOD__.': Raise new event: Modules/Course addParticipant');
			$ilAppEventHandler->raise("Modules/Course", "addParticipant", array('usr_id' => $a_usr_id,'role_id' => $a_role));
		}
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
		if(!ilObjUser::_isDesktopItem($a_usr_id, $this->ref_id,$this->type))
		{
			ilObjUser::_addDesktopItem($a_usr_id, $this->ref_id,$this->type);
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
		if(ilObjUser::_isDesktopItem($a_usr_id, $this->ref_id,$this->type))
		{
			ilObjUser::_dropDesktopItem($a_usr_id, $this->ref_id,$this->type);
		}

		return true;
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
	 * Read participants
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function readParticipants()
	{
		global $rbacreview,$ilObjDataCache,$ilLog;

		$rolf = $rbacreview->getRoleFolderOfObject($this->ref_id);
		if(!isset($rolf['ref_id']) or !$rolf['ref_id'])
		{
			$title = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->ref_id));
			$ilLog->write(__METHOD__.': Found object without role folder. Ref_id: '.$this->ref_id.', title: '.$title);
			$ilLog->logStack();
			return false;
		}
		
		$this->roles = $rbacreview->getRolesOfRoleFolder($rolf['ref_id'],false);

		$users = array();
		$this->participants = array();
		$this->members = $this->admins = $this->tutors = array();
		foreach($this->roles as $role_id)
		{
			$title = $ilObjDataCache->lookupTitle($role_id);
			switch(substr($title,0,8))
			{
				case 'il_crs_m':
					$this->role_data[IL_CRS_MEMBER] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->members = array_unique(array_merge($assigned,$this->members));
					break;

				case 'il_crs_a':
					$this->role_data[IL_CRS_ADMIN] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->admins = $rbacreview->assignedUsers($role_id);
					break;
		
				case 'il_crs_t':
					$this->role_data[IL_CRS_TUTOR] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->tutors = $rbacreview->assignedUsers($role_id);
					break;
					
				case 'il_grp_a':
					$this->role_data[IL_GRP_ADMIN] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->admins = $rbacreview->assignedUsers($role_id);
					break;
					
				case 'il_grp_m':
					$this->role_data[IL_GRP_MEMBER] = $role_id;
					$this->participants = array_unique(array_merge($assigned = $rbacreview->assignedUsers($role_id),$this->participants));
					$this->members = $rbacreview->assignedUsers($role_id);
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
	 		"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ";
	 	$res = $ilDB->query($query);
	 	$this->participants_status = array();
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->participants_status[$row->usr_id]['blocked'] = $row->blocked;
	 		$this->participants_status[$row->usr_id]['notification']  = $row->notification;
	 		$this->participants_status[$row->usr_id]['passed'] = $row->passed;
	 	}
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
	 * get all subscribers
	 *
	 * @access public
	 */
	public function getSubscribers()
	{
		$this->readSubscribers();

		return $this->subscribers;
	}

	
	/**
	 * get number of subscribers
	 *
	 * @access public
	 */
	public function getCountSubscribers()
	{
		return count($this->getSubscribers());
	}

	/**
	 * get subscriber data
	 *
	 * @access public
	 */
	public function getSubscriberData($a_usr_id)
	{
		return $this->readSubscriberData($a_usr_id);
	}



	/**
	 * Assign subscribers
	 *
	 * @access public
	 */
	public function assignSubscribers($a_usr_ids)
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

	/**
	 * Assign subscriber
	 *
	 * @access public
	 */
	public function assignSubscriber($a_usr_id)
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

		// TODO: must be group or course member role
		$this->add($tmp_obj->getId(),IL_CRS_MEMBER);
		$this->deleteSubscriber($a_usr_id);

		return true;
	}

	/**
	 * Assign subscriber
	 *
	 * @access public
	 */
	public function autoFillSubscribers()
	{
		$this->readSubscribers();

		$counter = 0;
		foreach($this->subscribers as $subscriber)
		{
			if(!$this->assignSubscriber($subscriber))
			{
				continue;
			}
			else
			{
				// TODO: notification
				#$this->sendNotification($this->NOTIFY_ACCEPT_SUBSCRIBER,$subscriber);
			}
			++$counter;
		}

		return $counter;
	}

	/**
	 * Add subscriber
	 *
	 * @access public
	 */
	public function addSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "INSERT INTO il_subscribers (usr_id,obj_id,subject,sub_time) ".
			" VALUES (".
			$ilDB->quote($a_usr_id ,'integer').",".
			$ilDB->quote($this->obj_id ,'integer').", ".
			$ilDB->quote('','text').", ".
			$ilDB->quote(time() ,'integer').
			")";
		$res = $ilDB->manipulate($query);

		return true;
	}


	/**
	 * Update subscription time
	 *
	 * @access public
	 */
	public function updateSubscriptionTime($a_usr_id,$a_subtime)
	{
		global $ilDB;

		$query = "UPDATE il_subscribers ".
			"SET sub_time = ".$ilDB->quote($a_subtime ,'integer')." ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}
	
	/**
	 * update subject
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateSubject($a_usr_id,$a_subject)
	{
		global $ilDB;
		
		$query = "UPDATE il_subscribers ".
			"SET subject = ".$ilDB->quote($a_subject ,'text')." ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);
		return true;
	}

	
	/**
	 * Delete subsciber
	 *
	 * @access public
	 */
	public function deleteSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM il_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}

	
	/**
	 * Delete subscibers
	 *
	 * @access public
	 */
	public function deleteSubscribers($a_usr_ids)
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
	
	
	/**
	 * check if is subscriber
	 *
	 * @access public
	 */
	public function isSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM il_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer')."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}

	/**
	 * check if user is subscriber
	 *
	 * @access public
	 * @static
	 */
	public static function _isSubscriber($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM il_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id ,'integer')."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * read subscribers
	 *
	 * @access protected
	 */
	protected function readSubscribers()
	{
		global $ilDB;

		$this->subscribers = array();

		$query = "SELECT usr_id FROM il_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
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

	/**
	 * read subscribers
	 *
	 * @access protected
	 */
	protected function readSubscriberData($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM il_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer')."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data["time"] = $row->sub_time;
			$data["usr_id"] = $row->usr_id;
			$data['subject'] = $row->subject;
		}
		return $data ? $data : array();
	}
}
?>