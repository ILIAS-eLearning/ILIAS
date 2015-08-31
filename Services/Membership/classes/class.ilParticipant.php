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


include_once './Services/Membership/classes/class.ilParticipants.php';

/**
* Base class for course and group participant
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership
*/
abstract class ilParticipant
{
	private $obj_id = 0;
	private $usr_id = 0;
	protected $type = '';
	private $ref_id = 0;
	
	private $component = '';

	private $participants = false;
	private $admins = false;
	private $tutors = false;
	private $members = false;
	
	private $numMembers = 0;

	private $participants_status = array();

	/**
	 * Singleton Constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	protected function __construct($a_component_name, $a_obj_id, $a_usr_id)
	{
	 	global $ilDB,$lng;
	 	
	 	$this->obj_id = $a_obj_id;
		$this->usr_id = $a_usr_id;
	 	$this->type = ilObject::_lookupType($a_obj_id);
		$ref_ids = ilObject::_getAllReferences($this->obj_id);
		$this->ref_id = current($ref_ids);
		
		$this->component = $a_component_name;
	 	
	 	$this->readParticipant();
	 	$this->readParticipantStatus();
	}
	
	/**
	 * Get component name
	 * Used for event handling
	 * @return type
	 */
	protected function getComponent()
	{
		return $this->component;
	}

	/**
	 * get user id
	 * @return int
	 */
	public function getUserId()
	{
		return $this->usr_id;
	}

	public function isBlocked()
	{
		return (bool) $this->participants_status[$this->getUserId()]['blocked'];
	}

	public function isAssigned()
	{
		return (bool) $this->participants;
	}

	public function isMember()
	{
		return (bool) $this->members;
	}

	public function isAdmin()
	{
		return $this->admins;
	}

	public function isTutor()
	{
		return (bool) $this->tutors;
	}

	public function isParticipant()
	{
		return (bool) $this->participants;
	}
	
	public function getNumberOfMembers()
	{
		return $this->numMembers;
	}
	

	/**
	 * Read participant
	 * @return void
	 */
	protected function readParticipant()
	{
		global $rbacreview,$ilObjDataCache,$ilLog;

		$this->roles = $rbacreview->getRolesOfRoleFolder($this->ref_id,false);

		$users = array();
		$this->participants = array();
		$this->members = $this->admins = $this->tutors = array();
		
		$member_roles = array();

		foreach($this->roles as $role_id)
		{
			$title = $ilObjDataCache->lookupTitle($role_id);
			switch(substr($title,0,8))
			{
				case 'il_crs_m':
					$member_roles[] = $role_id;
					$this->role_data[IL_CRS_MEMBER] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->members = true;
					}
					break;

				case 'il_crs_a':
					$this->role_data[IL_CRS_ADMIN] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->admins = true;
					}
					break;

				case 'il_crs_t':
					$this->role_data[IL_CRS_TUTOR] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->tutors = true;
					}
					break;

				case 'il_grp_a':
					$this->role_data[IL_GRP_ADMIN] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->admins = true;
					}
					break;

				case 'il_grp_m':
					$member_roles[] = $role_id;
					$this->role_data[IL_GRP_MEMBER] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->members = true;
					}
					break;

				default:
					
					$member_roles[] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->members = true;
					}
					break;
			}
		}
		$this->numMembers = $rbacreview->getNumberOfAssignedUsers((array) $member_roles);
	}

	/**
	 * Read participant status
	 * @global ilDB $ilDB
	 */
	protected function readParticipantStatus()
	{
	 	global $ilDB;

	 	$query = "SELECT * FROM obj_members ".
	 		"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
			'AND usr_id = '.$ilDB->quote($this->getUserId(),'integer');

	 	$res = $ilDB->query($query);
	 	$this->participants_status = array();
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->participants_status[$this->getUserId()]['blocked'] = $row->blocked;
	 		$this->participants_status[$this->getUserId()]['notification']  = $row->notification;
	 		$this->participants_status[$this->getUserId()]['passed'] = $row->passed;
	 	}
	}
	
	/**
	 * Add user to course
	 *
	 * @access public
	 * @param int user id
	 * @param int role IL_CRS_ADMIN || IL_CRS_TUTOR || IL_CRS_MEMBER
	 *
	 * global ilRbacReview $rbacreview
	 * 
	 */
	public function add($a_usr_id,$a_role)
	{
	 	global $rbacadmin,$ilLog,$ilAppEventHandler,$rbacreview;
	 	

		if($rbacreview->isAssignedToAtLeastOneGivenRole($a_usr_id,$this->roles))
		{
			return false;
		}
	 	
	 	switch($a_role)
	 	{
	 		case IL_CRS_ADMIN:
	 			$this->admins = true;
	 			break;

	 		case IL_CRS_TUTOR:
	 			$this->tutors = true;
	 			break;

	 		case IL_CRS_MEMBER:
	 			$this->members = true;	 			
	 			break;
	 			
	 		case IL_GRP_ADMIN:
	 			$this->admins = true;
	 			break;
	 			
	 		case IL_GRP_MEMBER:
	 			$this->members = true;
	 			break;
	 	}

		$rbacadmin->assignUser($this->role_data[$a_role],$a_usr_id);
		$this->addDesktopItem($a_usr_id);
		
		// Delete subscription request
		$this->deleteSubscriber($a_usr_id);
		
		include_once './Services/Membership/classes/class.ilWaitingList.php';
		ilWaitingList::deleteUserEntry($a_usr_id,$this->obj_id);

		$ilLog->write(__METHOD__.': Raise new event: Modules/Course addParticipant');
		$ilAppEventHandler->raise(
				$this->getComponent(),
				"addParticipant", 
				array(
					'obj_id' => $this->obj_id, 
					'usr_id' => $a_usr_id,
					'role_id' => $a_role)
		);
	 	return true;
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
		global $rbacadmin,$ilDB, $ilAppEventHandler;
		
		$this->dropDesktopItem($a_usr_id);
		foreach($this->roles as $role_id)
		{
			$rbacadmin->deassignUser($role_id,$a_usr_id);
		}
		
		$query = "DELETE FROM obj_members ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id ,'integer')." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id ,'integer');
		$res = $ilDB->manipulate($query);
		
		$ilAppEventHandler->raise(
				"Modules/Course", 
				"deleteParticipant", 
				array(
					'obj_id' => $this->obj_id, 
					'usr_id' => $a_usr_id)
		);
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

		$query = "SELECT * FROM obj_members ".
			"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE obj_members SET ".
				"notification = ".$ilDB->quote((int) $a_notification ,'integer')." ".
				"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id ,'integer');
		}
		else
		{
			$query = "INSERT INTO obj_members (notification,obj_id,usr_id,passed,blocked) ".
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
	 * Check if user for deletion are last admins
	 *
	 * @access public
	 * @param array array of user ids for deletion
	 * 
	 */
	public function checkLastAdmin($a_usr_ids)
	{
		global $ilDB;
		
		$admin_role_id = 
			$this->type == 'crs' ? 
			$this->role_data[IL_CRS_ADMIN] :
			$this->role_data[IL_GRP_ADMIN];
		
		
		$query = "
		SELECT			COUNT(rolesusers.usr_id) cnt
		
		FROM			object_data rdata
		
		LEFT JOIN		rbac_ua  rolesusers		
		ON				rolesusers.rol_id = rdata.obj_id
		
		WHERE			rdata.obj_id = %s
		";
		
		$query .= ' AND '.$ilDB->in('rolesusers.usr_id', $a_usr_ids, true, 'integer');		
		$res = $ilDB->queryF($query, array('integer'), array($admin_role_id));		

		$data = $ilDB->fetchAssoc($res);
					
		return (int)$data['cnt'] > 0;	
	}


}
?>
