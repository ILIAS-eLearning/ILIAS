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
* Class ilObjGroup
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

//TODO: function getRoleId($groupRole) returns the object-id of grouprole

require_once "class.ilObject.php";
require_once "class.ilGroupTree.php";

class ilObjGroup extends ilObject
{
	var $ref_grpId;

	var $obj_grpId;

	var $m_grpStatus;

	var $m_roleMemberId;

	var $m_roleAdminId;

	var $grp_tree;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjGroup($a_id = 0,$a_call_by_reference = true)
	{
		global $tree;

		$this->tree =& $tree;

		$this->type = "grp";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* join Group, assigns user to role
	* @access	private
	* @param	integer	member status = obj_id of local_group_role
	*/
	function join($a_user_id, $a_mem_role="")
	{
		global $rbacadmin;
		if(is_array($a_mem_role))
		{
			foreach($a_mem_role as $role)
			{
				$rbacadmin->assignUser($role,$a_user_id, false);
			}
		}
		else
		{
			$rbacadmin->assignUser($a_mem_role,$a_user_id, false);
		}
		ilObjUser::updateActiveRoles($a_user_id);
		return true;
	}

	/**
	* returns object id of created default member role
	* @access	public
	*/
	function getDefaultMemberRole()
	{
		$local_group_Roles = $this->getLocalGroupRoles();
		return $local_group_Roles["il_grp_member_".$this->getRefId()];
	}

	/**
	* returns object id of created default adminstrator role
	* @access	public
	*/
	function getDefaultAdminRole()
	{
		$local_group_Roles = $this->getLocalGroupRoles();
		return $local_group_Roles["il_grp_admin_".$this->getRefId()];
	}

	/**
	* add Member to Group
	* @access	public
	* @param	integer	user_id
	* @param	integer	member role_id of local group_role
	*/
	function addMember($a_user_id, $a_mem_role)
	{
		global $rbacadmin;
		if(isset($a_user_id) && isset($a_mem_role) )
		{
			$this->join($a_user_id,$a_mem_role);
			return true;
		}
		else
		{
			$this->ilias->raiseError(get_class($this)."::addMember(): Missing parameters !",$this->ilias->error_obj->WARNING);
			return false;
		}

	}

	/**
	* displays list of applicants
	* @access	public
	*/
	function getNewRegistrations()
	{
		$appList = array();
		$q = "SELECT * FROM grp_registration WHERE grp_id=".$this->getId();
		$res = $this->ilias->db->query($q);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($appList,$row);
		}
		return $appList;
	}

	/**
	* deletes an Entry from application list
	* @access	public
	*/
	function deleteApplicationListEntry($a_userId)
	{
		$q = "DELETE FROM grp_registration WHERE user_id=".$a_userId." AND grp_id=".$this->getId();
		$res = $this->ilias->db->query($q);
	}

	/**
	* is called when a member decides to leave group
	* @access	public
	* @param	integer	user-Id
	* @param	integer group-Id
	*/
	function leaveGroup()
	{
		global $rbacadmin, $rbacreview;

		$member_ids = $this->getGroupMemberIds();
		if(count($member_ids) <= 1 || !in_array($this->ilias->account->getId(), $member_ids))
			return 2;
		else
		{
			if(!$this->isAdmin($this->ilias->account->getId()))
			{
				$this->leave($this->ilias->account->getId());
				$member = new ilObjUser($this->ilias->account->getId());
				$member->dropDesktopItem($this->getRefId(), "grp");
				return 0;
			}
			else if(count($this->getGroupAdminIds()) == 1)
			{
				return 1;
			}
		}
	}

	/**
	* deassign member from group role
	* @access	private
	*/
	function leave($a_user_id)
	{
		global $rbacadmin;
		$arr_groupRoles = $this->getMemberRoles($a_user_id);
		if(is_array($arr_groupRoles))
		{
			foreach($arr_groupRoles as $groupRole)
			{
				$rbacadmin->deassignUser($groupRole, $a_user_id);
			}
		}
		else
		{
			$rbacadmin->deassignUser($arr_groupRoles, $a_user_id);
		}
		ilObjUser::updateActiveRoles($a_user_id);
		return true;
	}

	/**
	* removes Member from group
	* @access	public
	*/
	function removeMember($a_user_id, $a_grp_id="")
	{
		if( isset($a_user_id) && isset($a_grp_id) && $this->isMember($a_user_id))
		{
			if( count($this->getGroupMemberIds()) > 1)
			{
				if( $this->isAdmin($a_user_id) && count($this->getGroupAdminIds()) < 2)
				{
					return "grp_err_administrator_required";
				}
				else
				{
					$this->leave($a_user_id);
					$member = new ilObjUser($a_user_id);
					$member->dropDesktopItem($this->getRefId(), "grp");
					return "";
				}
			}
			else
			{
				return "grp_err_last_member";
			}
		}
		else
		{
			$this->ilias->raiseError(get_class($this)."::removeMember(): Missing parameters !",$this->ilias->error_obj->WARNING);
		}
	}

	/**
	* get group Members
	* @access	public
	* @param	integer	group id
	* @param	return array of users (obj_ids) that are assigned to the groupspecific roles (grp_member,grp_admin)
	*/
	function getGroupMemberIds($a_grpId="")
	{
		global $rbacadmin, $rbacreview;

		if(!empty($a_grpId) )
			$grp_id = $a_grpId;
		else
			$grp_id = $this->getRefId();

		$usr_arr= array();

		$rol  = $this->getLocalGroupRoles($grp_id);

		foreach ($rol as $value)
		{
			foreach ($rbacreview->assignedUsers($value) as $member_id)
			{
				array_push($usr_arr,$member_id);
			}
		}
		$mem_arr = array_unique($usr_arr);
		return $mem_arr;
	}

	/**
	* get Group Admin Id
	* @access	public
	* @param	integer	group id
	* @param	returns userids that are assigned to a group administrator! role
	*/
	function getGroupAdminIds($a_grpId="")
	{
		global $rbacreview;

		if(!empty($a_grpId) )
			$grp_id = $a_grpId;
		else
			$grp_id = $this->getRefId();

		$usr_arr = array();
		$roles = $this->getDefaultGroupRoles($this->getRefId());
		foreach ($rbacreview->assignedUsers($this->getDefaultAdminRole()) as $member_id)
		{
			array_push($usr_arr,$member_id);
		}

		return $usr_arr;
	}

	/**
	* get default group roles, returns the defaultlike create roles il_grp_member, il_grp_admin
	* @access	public
	* @param 	returns the obj_ids of group specific roles(il_grp_member,il_grp_admin)
	*/
	function getDefaultGroupRoles($a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		if(strlen($a_grp_id) > 0)
		{
			$grp_id = $a_grp_id;
		}
		else
		{
			$grp_id = $this->getRefId();
		}

		//$rolf 	   = $rbacreview->getRoleFolderOfObject($this->getRefId());
		$rolf 	   = $rbacreview->getRoleFolderOfObject($grp_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

			$grp_Member ="il_grp_member_".$grp_id;
			$grp_Admin  ="il_grp_admin_".$grp_id;

			if(strcmp($role_Obj->getTitle(), $grp_Member) == 0 )
				$arr_grpDefaultRoles["grp_member_role"] = $role_Obj->getId();

			if(strcmp($role_Obj->getTitle(), $grp_Admin) == 0 )
				$arr_grpDefaultRoles["grp_admin_role"] = $role_Obj->getId();
		}

		return $arr_grpDefaultRoles;

	}

	/**
	* get ALL local roles of group, also those created and defined afterwards
	* @access	public
	* @param	return array [title|id] of roles...
	*/
	function getLocalGroupRoles($a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		if(strlen($a_grp_id) > 0)
			$grp_id = $a_grp_id;
		else
			$grp_id = $this->getRefId();
		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->getRefId());
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			if($rbacreview->isAssignable($role_id,$rolf["ref_id"])==true )
			{
				$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);
				$arr_grpDefaultRoles[$role_Obj->getTitle()] = $role_Obj->getId();
			}
		}
		return $arr_grpDefaultRoles;
	}

	/**
	* get group status closed template
	* @access	public
	* @param	return obj_id of roletemplate containing permissionsettings for a closed group
	*/
	function getGrpStatusClosedTemplateId()
	{
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_closed'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row["obj_id"];
	}

	/**
	* get group status open template
	* @access	public
	* @param	return obj_id of roletemplate containing permissionsettings for an open group
	*/
	function getGrpStatusOpenTemplateId()
	{
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_open'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row["obj_id"];
	}
	
	/**
	* set Registration Flag 
	* @access	public
	* @param	integer [ 0 = no registration| 1 = registration]
	*/
	function setRegistrationFlag($a_regFlag="")
	{
		$q = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res = $this->ilias->db->query($q);

		if(!isset($a_regFlag))
			$a_regFlag = 0;

		if($res->numRows() == 0)
		{
			$q = "INSERT INTO grp_data (grp_id, register) VALUES(".$this->getId().",".$a_regFlag.")";
			$res = $this->ilias->db->query($q);
		}
		else
		{
			$q = "UPDATE grp_data SET register=".$a_regFlag." WHERE grp_id=".$this->getId()."";
			$res = $this->ilias->db->query($q);
		}
	}

	/**
	* get Registration Flag
	* @access	public
	* @param	return flag => [ 0 = no registration| 1 = registration]
	*/
	function getRegistrationFlag()
	{
		$q = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row["register"];
	}

	/**
	* get Password
	* @access	public
	* @param	return password
	*/
	function getPassword()
	{
		$q = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row["password"];
	}
	
	/**
	* set Password
	* @access	public
	* @param	password
	*/
	function setPassword($a_password="")
	{
		$q = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res = $this->ilias->db->query($q);

		if($res->numRows() == 0)
		{
			$q = "INSERT INTO grp_data (grp_id, password) VALUES(".$this->getId().",'".$a_password."')";
			$res = $this->ilias->db->query($q);
		}
		else
		{
			$q = "UPDATE grp_data SET password='".$a_password."' WHERE grp_id=".$this->getId()."";
			$res = $this->ilias->db->query($q);
		}
	}

	/**
	* set Expiration Date and Time
	* @access	public
	* @param	date
	*/
	function setExpirationDateTime($a_date)
	{
		$q = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res = $this->ilias->db->query($q);
		$date = ilFormat::input2date($a_date);
		if($res->numRows() == 0)
		{
			$q = "INSERT INTO grp_data (grp_id, expiration) VALUES(".$this->getId().",'".$date."')";
			$res = $this->ilias->db->query($q);
		}
		else
		{
			$q = "UPDATE grp_data SET expiration='".$date."' WHERE grp_id=".$this->getId()."";
			$res = $this->ilias->db->query($q);
		}
	}

	/**
	* get Expiration Date and Time
	* @access	public
	* @param	return array(0=>date, 1=>time)
	*/
	function getExpirationDateTime()
	{
		$q = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$datetime = $row["expiration"];
		$date = ilFormat::fdateDB2dateDE($datetime);
		$time = substr($row["expiration"], -8);
		$datetime = array(0=>$date, 1=>$time);
		return $datetime;
	}

	function registrationPossible()
	{
		$datetime = $this->getExpirationDateTime();
		$today_date = ilFormat::getDateDE();
		$today_time = date("H:i:s");
       
		$ts_exp_date = ilFormat::dateDE2timestamp($datetime[0]);
		$ts_today_date = ilFormat::dateDE2timestamp($today_date);
	
		$ts_exp_time = substr($datetime[1], 0, 2).
						substr($datetime[1], 3, 2).
						substr($datetime[1], 6, 2);
		
		$ts_today_time = substr($today_time, 0, 2).
						substr($today_time, 3, 2).
						substr($today_time, 6, 2);
		
		if ( $ts_today_date < $ts_exp_date ) 
		{
			return true;
		}
		elseif ( ($ts_today_date == $ts_exp_date) and (strcmp($ts_exp_time,$ts_today_time) >= 0) ) 
		{
			return true;
		}
		else 
		{
			return false;
		}
		 	
	}

	/**
	* set group status
	* @access	public
	* @param	integer	group id (optional)
	* @param	integer group status (0=public|1=private|2=closed)
	*/
	function setGroupStatus($a_grpStatus)
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		//get Rolefolder of group
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());

		//define all relevant roles that rights are needed to be changed
		$arr_globalRoles = array_diff(array_keys($rbacreview->getParentRoleIds($this->getRefId())),$this->getDefaultGroupRoles());

		//group status opened/private
	  	if ($a_grpStatus == 0 )//|| $a_grpStatus == 1)
		{

			//get defined operations on object group depending on group status "CLOSED"->template 'il_grp_status_closed'
			$arr_ops = $rbacreview->getOperationsOfRole($this->getGrpStatusOpenTemplateId(), 'grp', ROLE_FOLDER_ID);

			foreach ($arr_globalRoles as $globalRole)
			{
				//initialize permissionarray
				$granted_permissions = array();
				//delete old rolepermissions in rbac_fa
				$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);
				//revoke all permission on current group object for global role
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);
				//grant new permissions according to group status
				//$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());
				$ops_of_role = $rbacreview->getOperationsOfRole($globalRole,"grp", ROLE_FOLDER_ID);

				if(in_array(2,$ops_of_role)) //VISIBLE permission is set for global role and group
				{
					array_push($granted_permissions,"2");
				}
				if(in_array(7,$ops_of_role)) //JOIN permission is set for global role and group
				{
					array_push($granted_permissions,"7");
				}

				if(!empty($granted_permissions))
				{
					$rbacadmin->grantPermission($globalRole, $granted_permissions, $this->getRefId());
				}

				//copy permissiondefinitions of openGroup_template
				$rbacadmin->copyRolePermission($this->getGrpStatusOpenTemplateId(),ROLE_FOLDER_ID,$rolf_data["child"],$globalRole);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte Ã¼bernehmen soll
				//$rbacadmin->copyRolePermission($this->getGrpStatusOpenTemplateId(),8,$rolf_data["child"],$globalRole);
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],"false");
			}//END foreach
		}

		//group status closed
	  	if($a_grpStatus == 1)
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'il_grp_status_closed'
			$arr_ops = $rbacreview->getOperationsOfRole($this->getGrpStatusClosedTemplateId(), 'grp', ROLE_FOLDER_ID);
			foreach ($arr_globalRoles as $globalRole)
			{
				//delete old rolepermissions in rbac_fa
				$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);
				//revoke all permission on current group object for all(!) global roles, may be a workaround
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);//refid des grpobjektes,dass rechte aberkannt werden, opti.:roleid, wenn nur dieser rechte aberkannt...
				//set permissions of global role (admin,author,guest,learner) for group object
				$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());//rollenid,operationen,refid des objektes auf das rechte gesetzt werden
				//copy permissiondefinitions of closedGroup_template
				$rbacadmin->copyRolePermission($this->getGrpStatusClosedTemplateId(),ROLE_FOLDER_ID,$rolf_data["child"],$globalRole);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte Ã¼bernehmen soll
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],"false");
			}//END foreach
		}
	}

	/**
	* get group status, redundant method because
	* @access	public
	* @param	return group status[0=public|2=closed]
	*/
	function getGroupStatus()
	{
		global $rbacsystem,$rbacreview;

		$role_folder = $rbacreview->getRoleFolderOfObject($this->getRefId());
		$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);

		//get Rolefolder of group
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());
		//get all relevant roles
		$arr_globalRoles = array_diff($local_roles, $this->getDefaultGroupRoles());

		//if one global role has no permission to join the group is officially closed !
		foreach($arr_globalRoles as $globalRole)
		{
			$ops_of_role = $rbacreview->getOperationsOfRole($globalRole,"grp", ROLE_FOLDER_ID);
			if ($rbacsystem->checkPermission($this->getRefId(), $globalRole ,"join"))
			{
				return 0;
			}
		}

		return 1;
	}

	/**
	* get group member status
	* @access	public
	* @param	returns array of obj_ids of assigned local roles
	*/
	function getMemberRoles($a_user_id, $a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		$arr_assignedRoles = array();

		if(strlen($a_grp_id) > 0)
			$grp_id = $a_grp_id;
		else
			$grp_id = $this->getRefId();

		$roles = $this->getLocalGroupRoles($grp_id);

		foreach($roles as $role)
		{
			if( in_array($a_user_id,$rbacreview->assignedUsers($role) ))
			{
				array_push($arr_assignedRoles, $role);
			}
		}
		//return $role;
		return $arr_assignedRoles;
	}

	/**
	* set member status
	* @access	public
	* @param	integer	user id
	* @param	integer member role id
	*/
	function setMemberStatus($a_user_id, $a_member_role)
	{
		if(isset($a_user_id) && isset($a_member_role))
		{
			$this->removeMember($a_user_id);
			$this->addMember($a_user_id, $a_member_role);
		}
	}

	/**
	* is Member
	* @access	public
	* @param	integer	user_id
	* @param	return true if user is member
	*/
	function isMember($a_userId="")
	{
		global $rbacadmin, $rbacreview, $ilias;

		if (strlen($a_userId) == 0)
		{
			$user_id = $this->ilias->account->getId();
		}
		else 
		{
			$user_id = $a_userId;
		}
		
		if($this->getType() == "grp")
		{

			$arr_members = $this->getGroupMemberIds();
			if(in_array($user_id, $arr_members))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	* is Admin
	* @access	public
	* @param	integer	user_id
	* @param	boolean, true if user is group administrator
	*/
	function isAdmin($a_userId)
	{
		global $rbacreview;
		$grp_Roles = $this->getDefaultGroupRoles();
		if( in_array($a_userId,$rbacreview->assignedUsers($grp_Roles["grp_admin_role"]) ))
			return true;
		else
			return false;
	}

	/**
	* create new group tree
	* @access	public
	* @param	integer	reference id of object
	*/
	function createNewGroupTree($objGrpRefId)
	{
		$grp_tree = new ilGroupTree($objGrpRefId);

		$grp_tree->addTree($objGrpRefId);

		$q1 = "UPDATE grp_tree SET perm=1 WHERE parent=0 AND child=".$objGrpRefId;
		$this->ilias->db->query($q1);

		$objGrp =& $this->ilias->obj_factory->getInstanceByRefId($objGrpRefId);
		$objGrpId = $objGrp->getId();

		$q2 = "UPDATE grp_tree SET obj_id=".$objGrpId." WHERE parent=0 AND child=".$objGrpRefId;
		$this->ilias->db->query($q2);
	}

	/**
	* copies a grouptree with a new ref_id
	* (explanation follows later)
	*
	* @access	private
	* @param	integer	ref_id	new reference id of current object (created by clone method)
	* @return	boolean	true on success
	*/
	function copyOldGroupTree($a_new_ref_id,$a_new_obj_id)
	{
		$q = "SELECT * FROM grp_tree WHERE tree='".$this->getRefId()."'";
		$r1 = $this->ilias->db->query($q);

		while ($row = $r1->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$q = "INSERT INTO grp_tree (tree,child,parent,lft,rgt,depth,perm,obj_id) ".
				 "VALUES ".
				 "('".$a_new_ref_id."','".$row->child."','".$row->parent."','".$row->lft."','".$row->rgt."'".
				 ",'".$row->depth."','".$row->perm."','".$row->obj_id."')";
			$this->ilias->db->query($q);
		} // while
		
		$q = "UPDATE grp_tree SET child='".$a_new_ref_id."',obj_id='".$a_new_obj_id."' ".
			 "WHERE tree='".$a_new_ref_id."' AND child='".$this->getRefId()."'";
		$this->ilias->db->query($q);
	}
	
	/**
	* @param	integer	ref_id of the new object
	* @param	integer	ref_id of of the parent node
	* @param	integer	ref_id of the group (tree_id)
	* @param	(obsolete)integer	obj_id of the new object
	*/
	function insertGroupNode($new_node_ref_id,$parent_ref_id,$grp_tree_id,$new_node_obj_id=-1 )
	{	
		$grp_tree = new ilGroupTree($grp_tree_id);
		
		$grp_tree->insertNode($new_node_ref_id,$parent_ref_id);
		
		$new_node_obj=& $this->ilias->obj_factory->getInstanceByRefId($new_node_ref_id);
		
		if ($new_node_obj->getType()=="fold" or $new_node_obj->getType()=="file")
		{
			$q1 = "UPDATE grp_tree SET perm=0 WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
			$this->ilias->db->query($q1);
		}
		else
		{
			$q1 = "UPDATE grp_tree SET perm=1 WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
			$this->ilias->db->query($q1);
		}
		
		$q2 = "UPDATE grp_tree SET obj_id=".$new_node_obj->getId()." WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
		$this->ilias->db->query($q2);
		
	}
	
	/**
	* copy all properties and subobjects of a group.
	* Does not copy the settings in the group's local role folder. Instead a new local role folder is created from
	* the template settings (same process as creating a new group manually)
	* 
	* @access	public
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);
		
		// get object instance of cloned group
		$groupObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);
		
		// first changed groupname to keep groupnames unique
		include_once "./classes/class.ilGroup.php";
		
		$grp = new ilGroup();
		
		// find a free number
		for ($n = 1;$n < 99;$n++)
		{
			$groupname_copy = $groupObj->getTitle()."_(copy_".$n.")";

			if (!$grp->groupNameExists($groupname_copy))
			{
				$groupObj->setTitle($groupname_copy);
				$groupObj->update();
				break;
			}
		}

		// setup rolefolder & default local roles (admin & member)
		$roles = $groupObj->initDefaultRoles();

		// ...finally assign groupadmin role to creator of group object
		$rbacadmin->assignUser($roles[0], $groupObj->getOwner(), "n");
		ilObjUser::updateActiveRoles($groupObj->getOwner());

		// TODO: function getGroupStatus returns integer but setGroupStatus expects a string.
		// I disabled this function. Please investigate
		// shofmann@databay.de	4.7.03
		// copy group status
		// 0=public/visible for all,1=closed/invisible for all
		$groupObj->setGroupStatus($this->getGroupStatus());

		// create new tree in "grp_tree" table; each group has his own tree in "grp_tree" table
		// copy all entries from copied group. the new ref ids of subobjects will be updated during the cloning process, because at this point
		// these values are not known yet
		$this->copyOldGroupTree($groupObj->getRefId(),$groupObj->getId());

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
		unset($groupObj);
		unset($rfoldObj);
		unset($roleObj);

		// session setzen
		$_SESSION["copied_group_refs"][$this->getRefId()] = $new_ref_id;

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete group and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		$nodes = $this->getNoneRbacObjects();

		foreach ($nodes as $node)
		{
			$obj = $this->ilias->obj_factory->getInstanceByRefId($node["child"]);
			$obj->delete();
			unset($obj);
		}

		$query = "DELETE FROM grp_tree WHERE tree=".$this->getRefId();
		$this->ilias->db->query($query);
		
		$query = "DELETE FROM grp_data WHERE grp_id=".$this->getId();
		$this->ilias->db->query($query);

		return true;
	}

	/**
	* init default roles settings
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin, $rbacreview;

		// create a local role folder
		$rfoldObj = $this->createRoleFolder();

		// ADMIN ROLE
		// create role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_grp_admin_".$this->getRefId(),"Groupadmin of group obj_no.".$this->getId());
		$this->m_roleAdminId = $roleObj->getId();

		//set permission template of new local role
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_admin'";
		$r = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRolePermission($r->obj_id,ROLE_FOLDER_ID,$rfoldObj->getRefId(),$roleObj->getId());

		// set object permissions of group object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"grp",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$this->getRefId());

		// set object permissions of role folder object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"rolf",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$rfoldObj->getRefId());

		// MEMBER ROLE
		// create role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_grp_member_".$this->getRefId(),"Groupmember of group obj_no.".$this->getId());
		$this->m_roleMemberId = $roleObj->getId();

		//set permission template of new local role
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_member'";
		$r = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRolePermission($r->obj_id,ROLE_FOLDER_ID,$rfoldObj->getRefId(),$roleObj->getId());
		
		// set object permissions of group object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"grp",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$this->getRefId());

		// set object permissions of role folder object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"rolf",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$rfoldObj->getRefId());

		unset($rfoldObj);
		unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	*checks if the object is already a node of the group's root
	*obj_id of the tree/group
	*obj_id of the node
	*/
	function objectExist($a_tree_id, $a_node_id)
	{//echo $a_tree_id."------".$a_node_id;
		$q = "SELECT tree FROM grp_tree ".
			"WHERE tree = '".$a_tree_id."' ".
			"AND parent = '".$a_tree_id."' ".
			"AND child  = '".$a_node_id."'";
		$r = $this->ilias->db->getRow($q);
		//echo $q;
		//echo "r_tree".$r->tree."r_tree";
		if (isset($r->tree))
		{
			return true;
		}else{
			return false;
		}
	}
	
	function removeDeletedNodesInGrpTree($a_node_id,$a_checked)
	{
		$grp_tree = new ilGroupTree($this->getRefId());
		
		$q = "SELECT tree FROM grp_tree WHERE parent='".$a_node_id."' AND tree < 0";
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{	
			// only continue recursion if fetched node wasn't touched already!
			if (!in_array($row->tree,$a_checked))
			{
				$deleted_tree = new ilGroupTree($row->tree);
				$a_checked[] = $row->tree;
			
				$row->tree = $row->tree * (-1);
				$del_node_data = $deleted_tree->getNodeData($row->child);
				//$del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);

				$this->removeDeletedNodesInGrpTree($row->child,$a_checked);
			
				/*foreach ($del_subtree_nodes as $node)
				{
					$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
					$node_obj->delete();
				}*/
			$grp_tree->deleteTree($del_node_data);

			}
		}
		
		return true;
	}
	
	function insertSavedNodesInGrpTree($a_source_id,$a_dest_id,$a_tree_id,$a_obj_id)
	{
		$grp_tree = new ilGroupTree($this->getRefId());
		$this->insertGroupNode($a_source_id,$a_dest_id,$this->getRefId(),(int)$a_obj_id);
		
		$saved_tree = new ilGroupTree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->insertSavedNodesInGrpTree($child["child"],$a_source_id,$a_tree_id,$a_obj_id);
		}
	}
	
	//delete node, which is in trash
	function removeSavedNodesFromGrpTree($a_delete_id)
	{
		$query = "SELECT tree FROM grp_tree  WHERE child=".$a_delete_id;
		$res = $this->ilias->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		
		$grp_tree = new ilGroupTree($row["tree"]);
		$grp_tree->deleteTree($grp_tree->getNodeData($a_delete_id));	
	}
	
	/**
	* updates the Group trees
	*
	* @access  public
	* @param	 integer	reference id of object where the event occured	
	*/
	function pasteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		$ref_ids = array_keys($a_params);
		$grp_id = ilUtil::getGroupId($ref_ids[0]);

		if ($grp_id !== false)
		{
			$old_grp_tree = new ilGroupTree($grp_id);

			foreach ($a_params as $ref_id => $subnode)
			{
				// get node data
				$top_node = $old_grp_tree->getNodeData($ref_id);
				// get subnodes of top nodes
				$subnodes[$ref_id] = $old_grp_tree->getSubtree($top_node);
			}
		}
		else
		{
			$subnodes = $a_params;
		}
//vd($a_params, $subnodes,$grp_id);exit;
		foreach ($subnodes as $ref_id => $subnode)
		{
			$this->insertGroupNode($ref_id,$a_ref_id,$this->getRefId());
			// ... remove top_node from list ...
			array_shift($subnode);
	
			// ... insert subtree of top_node if any subnodes exist
			if (count($subnode) > 0)
			{
				foreach ($subnode as $node)
				{
					$this->insertGroupNode($node["child"],$node["parent"],$this->getRefId());
				}
			}
		}
/*
	
		if ($_GET["parent_non_rbac_id"] > 0)
		{  
			foreach ($a_params as $parameter => $value)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($value);
				$this->insertGroupNode($new_node->getRefId(),$_GET["parent_non_rbac_id"],$this->getRefId(),$new_node->getId());
			}
		}
		else
		{
			$childrenNodes = $this->tree->getChilds($_GET["ref_id"]); 

			foreach ($childrenNodes as $child)
			{
				$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
			
				if (!$object->getRefId()==$grp_tree->getParentId($child["ref_id"]))
				{
					$this->insertGroupNode($child["ref_id"],$object->getRefId(),$this->getRefId(),$child["obj_id"]);

					//repeat the procedure one level deeper			
					$this->pasteGrpTree($child["ref_id"],$a_parent_non_rbac_id,$a_params);
				}
			}
		}*/
	}
	
	function cutGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{	
		$ref_ids = array_keys($a_params);
		$grp_id = ilUtil::getGroupId($ref_ids[0]);

	//	if ($grp_id !== false)
	//	{
		//	echo "grp ";
			$grp_tree = new ilGroupTree($this->getRefId());
		
			foreach ($a_params as $parameter => $value)
			{
				$note_data = $grp_tree->getNodeData($value);	
				$grp_tree->deleteTree($note_data);
			}
		//}
		//vd($a_params,$a_ref_id,$this->getRefId(),$grp_id,$ref_ids);exit;
	}

	function linkGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		$subnodes = $a_params;

		foreach ($subnodes as $ref_id => $subnode)
		{
			$this->insertGroupNode($ref_id,$a_ref_id,$this->getRefId());
			// ... remove top_node from list ...
			array_shift($subnode);
	
			// ... insert subtree of top_node if any subnodes exist
			if (count($subnode) > 0)
			{
				foreach ($subnode as $node)
				{
					$this->insertGroupNode($node["child"],$node["parent"],$this->getRefId());
				}
			}
		}

//vd($a_params);exit;
		//$grp_tree = new ilGroupTree($this->getRefId());
		
		/*foreach ($a_params as $new_ref_id => old_ref_id)
		{
			$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
			$this->insertGroupNode($new_node->getRefId(),$this->getRefId(),$this->getRefId(),$new_node->getId());
		}*/

			//get (direct) children of the node where the event occured
			/*$childrenNodes = $this->tree->getChilds($_GET["ref_id"]);
		
			//filter only the nodes which were linked
			foreach ( $childrenNodes as $child)
			{
				foreach ( $a_params as $parameter => $value)
				{
					if ( $child["ref_id"] == $parameter )
					{
						$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
					
						//insert the new node into the 'grp_tree' table	
						$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
						$this->insertGroupNode($new_node->getId(),$object->getId(),$this->getId(),$new_node->getRefId());

						$a_params = array_diff($a_params,array($value));
						
						//repeat the procedure one level deeper			
						$this->linkGrpTree($child["ref_id"],$a_parent_non_rbac_id,$a_params);  
					}
					
				}
			}*/
	}
	
	function newGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{  	
		//var_dump($a_parent_non_rbac_id,$a_ref_id);exit;
		
		if (empty($a_parent_non_rbac_id))
		{
			$a_parent_non_rbac_id = $a_ref_id;
		}
		
		$object =& $this->ilias->obj_factory->getInstanceByRefId($a_params);
		$this->insertGroupNode($object->getRefId(),$a_parent_non_rbac_id,$this->getRefId(),$object->getId());
	}
	
	function copyGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{	
		
		$grp_tree = new ilGroupTree($this->getRefId());
		
		if ($_GET["parent_non_rbac_id"] > 0)
		{
			foreach ($a_params as $parameter)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
				$this->insertGroupNode($new_node->getRefId(),$_GET["parent_non_rbac_id"],$this->getRefId(),$new_node->getId());
			}
		}
		else
		{	
			foreach ($a_params as $parameter)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
				$this->insertGroupNode($new_node->getRefId(),$this->getRefId(),$this->getRefId(),$new_node->getId());
			}
			/*//get (direct) children of the node where the event occured
			$childrenNodes = $this->tree->getChilds($_GET["ref_id"]); 
		
			//filter only the nodes which were linked
			foreach ( $childrenNodes as $child)
			{	
				foreach ( $a_params as $parameter => $value)
				{ 
					if ( $child["ref_id"] == $parameter )
					{
						$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
					
						//insert the new node into the 'grp_tree' table	
						$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
						$this->insertGroupNode($new_node->getId(),$object->getId(),$this->getId(),$new_node->getRefId());
						//var_dump($a_params);
						$a_params = array_diff($a_params,array($value));
						//var_dump($a_params);
						
						//repeat the procedure one level deeper			
						$this->copyGrpTree($child["ref_id"],$a_parent_non_rbac_id,$a_params);  
					}
					
				}
			}*/
		}
	}
	
	function confirmedDeleteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		$grp_tree = new ilGroupTree($this->getRefId());

		// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
		foreach ($a_params as $id)
		{
			$tmp_obj=& $this->ilias->obj_factory->getInstanceByRefId($id);
			$grp_tree->saveSubTree($tmp_obj->getRefId());
			$grp_tree->deleteTree($grp_tree->getNodeData($tmp_obj->getRefId()));
		}
	}
	
	function removeFromSystemGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		$grp_tree = new ilGroupTree($this->getRefId());
		
		// DELETE THEM
		foreach ($_POST["trash_id"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES

			$tmp_obj=& $this->ilias->obj_factory->getInstanceByRefId($id);
			$saved_tree = new ilGroupTree(-(int)$tmp_obj->getRefId());
			$node_data = $saved_tree->getNodeData($tmp_obj->getRefId());
			$subtree_nodes = $saved_tree->getSubTree($node_data);

			// remember already checked deleted node_ids
			$checked[] = -(int) $tmp_obj->getRefId();

			// dive in recursive manner in each already deleted subtrees and remove these objects too
			$this->removeDeletedNodesInGrpTree($tmp_obj->getRefId(),$checked);
			
			/*foreach ($subtree_nodes as $node)
			{
				$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
				$node_obj->delete();
			}*/

			// FIRST DELETE ALL ENTRIES IN GROUP TREE
			$grp_tree->deleteTree($node_data);
		}	
	}
	
	function undeleteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		foreach ($_POST["trash_id"] as $id)
		{
			$tmp_obj=& $this->ilias->obj_factory->getInstanceByRefId($id);
			//$dest_obj=& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
			$dest_obj=& $this->ilias->obj_factory->getInstanceByRefId($this->getRefId());
			
			// INSERT 
			$this->insertSavedNodesInGrpTree($tmp_obj->getRefId(),$dest_obj->getRefId(),-(int) $tmp_obj->getRefId(),$id);
			
			// DELETE SAVED TREE
			$this->removeSavedNodesFromGrpTree($tmp_obj->getRefId());
			//$saved_tree = new ilGroupTree(-(int)$tmp_obj->getRefId());
			//$saved_tree->deleteTree($saved_tree->getNodeData($tmp_obj->getRefId()));
		}
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		// object specific event handling
		global $tree;
		//var_dump("<pre>",$a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params,"</pre>");exit;
		switch ($a_event)
		{
			case "undelete":
				$this->undeleteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//exit;
				break;
			
			case "removeFromSystem":
				$this->removeFromSystemGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//var_dump("<pre>",$a_params,"</pre>");

				//exit;
				break;
			
			case "confirmedDelete":
				$this->confirmedDeleteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//var_dump("<pre>",$a_params,"</pre>");
				//exit;
				break;
			
			case "link":
				$this->linkGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Group ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				$this->cutGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
				
				//echo "cut";
				//echo "Group ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
				$this->copyGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Group ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":
				$this->pasteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
				
				//echo "Group ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
			//var_dump($a_params,$this->getRefId());exit;
				//avoids error during saving a new grp object
				if ($a_params != $this->getRefId())
				{
					$this->newGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
				}

				//echo "Group ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		/*if ($a_node_id==$_GET["ref_id"]) 
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}*/
		$a_node_id = (int) $tree->getParentId($a_node_id);
				
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}
	
	// get files and folders
	function getNoneRbacObjects()
	{
		$q = "SELECT child,parent FROM grp_tree WHERE tree='".$this->getRefId()."' AND perm=0";
		$r = $this->ilias->db->query($q);
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$arr[] = array(
						"child"		=> $row->child,
						"parent"	=> $row->parent
						);
		} // while
		
		return $arr ? $arr : array();
	}

	// clone files and folders
	function cloneNoneRbacObjects()
	{
		$tree_list = $this->getNoneRbacObjects();
		
		$cloned_objects = array();

		foreach ($tree_list as $data)
		{
			$obj = $this->ilias->obj_factory->getInstanceByRefId($data["child"]);
			$new_ref_id = $obj->clone();
			$cloned_objects[$data["child"]] = array(
													"new_ref"		=> $new_ref_id,
													"old_child"		=> $data["child"],
													"old_parent"	=> $data["parent"]
													);
		}
		
		$this->updateRbacObjectsInGroupTree($cloned_objects);
	}
	
	function updateRbacObjectsInGroupTree($a_cloned_objects)
	{
			//var_dump($a_cloned_objects);
		if (count($a_cloned_objects) == 0)
		{
			return;
		}

		foreach ($a_cloned_objects as $key => $clone)
		{
			// get lft,rgt from parent_node of old grp_tree
			$q = "SELECT lft,rgt,child FROM grp_tree ".
				 "WHERE child='".$clone["old_parent"]."' ";
			$r = $this->ilias->db->query($q);

			if ($r->numRows())
			{
				// get corresponding parent_node in new grp_tree
				$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
				
				if (!array_key_exists($row->child,$a_cloned_objects))
				{
					$q = "SELECT child FROM grp_tree ".
						 "WHERE lft='".$row->lft."' ".
						 "AND rgt='".$row->rgt."' ".
						 "AND tree='".$this->getRefId()."'";
					$r = $this->ilias->db->query($q);
					$row2 = $r->fetchRow(DB_FETCHMODE_OBJECT);
				
					// update new grp_tree
					$q = "UPDATE grp_tree SET child='".$clone["new_ref"]."', ".
						 "parent='".$row2->child."', obj_id=0 ".
						 "WHERE tree='".$this->getRefId()."' ".
						 "AND child='".$clone["old_child"]."' ".
						 "AND parent='".$clone["old_parent"]."'";
					$this->ilias->db->query($q);
				
					// remove update object from list
					unset($a_cloned_objects[$key]);
				}
			}
		} // foreach
		
		// repeat process while still objects in list
		if (count($a_cloned_objects > 0))
		{
			//var_dump($a_cloned_objects);
			$this->updateRbacObjectsInGroupTree($a_cloned_objects);
		}
	}
	
	// correcting structure of rbac objects in group
	function fixTreeStructure($a_old_tree)
	{
		$q = "SELECT lft,rgt FROM grp_tree WHERE tree='".$this->getRefId()."' AND perm = 1 AND parent != 0 ";
		$r = $this->ilias->db->query($q);
		
		$new_tree_nodes = array();
		
		while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$new_tree_nodes[] = array(
										"lft"		=> $row->lft,
										"rgt"		=> $row->rgt
										);
		}
		
		foreach ($new_tree_nodes as $node)
		{
			$q = "SELECT  t3.child ".
				 "FROM grp_tree AS t1, grp_tree AS t2, grp_tree AS t3 ".
				 "WHERE t1.lft = '".$node["lft"]."' AND t1.rgt = '".$node["rgt"]."' ".
				 "AND t1.parent = t2.child ".
				 "AND t2.lft = t3.lft AND t2.rgt = t3.rgt ".
				 "AND t1.tree = '".$a_old_tree."' ".
				 "AND t2.tree = '".$a_old_tree."' ".
				 "AND t3.tree = '".$this->getRefId()."'";
			$r = $this->ilias->db->query($q);
			
			$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
			echo $row->child;
			$q = "UPDATE grp_tree SET parent='".$row->child."' WHERE lft = '".$node["lft"]."' AND rgt = '".$node["rgt"]."' AND tree = '".$this->getRefId()."'";
			$this->ilias->db->query($q);
		}
	}


	/**
	 * STATIC METHOD
	 * search for group data. This method is called from class.ilSearch
	 * This method used by class.ilSearchGUI.php to a link to the results
	 * @param	object object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$a_search_obj)
	{
		// NO CLASS VARIABLES IN STATIC METHODS

		$where_condition = $a_search_obj->getWhereCondition("like",array("title","description"));
		$in = $a_search_obj->getInStatement("ore.ref_id");

		$query = "SELECT ore.ref_id AS ref_id FROM object_data AS od, object_reference AS ore ".
			$where_condition." ".
			$in." ".
			"AND od.obj_id = ore.obj_id ".
			"AND od.type = 'grp' ";

		$res = $a_search_obj->ilias->db->query($query);
		
		$counter = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_data[$counter++]["id"]				=  $row->ref_id;
			#$result_data[$counter]["link"]				=  "group.php?cmd=view&ref_id=".$row->ref_id;
			#$result_data[$counter++]["target"]			=  "";
		}
		return $result_data ? $result_data : array();
	}

	/**
	 * STATIC METHOD
	 * create a link to the object
	 * @param	int uniq id
	 * @return array array('link','target')
	 * @static
	 * @access	public
	 */
	function _getLinkToObject($a_id)
	{
		return array("group.php?cmd=view&ref_id=".$a_id,"");
	}
} //END class.ilObjGroup
?>
