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
require_once "class.perm.php";

require_once "class.ilObjectGUI.php";

class ilObjGroup extends ilObject
{
	var $m_grpId;

	var $m_grpStatus;
	
	var $ilias;

	var $tree;
	
	var $m_roleMemberId;

	var $m_roleAdminId;	
	

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjGroup($a_id = 0,$a_call_by_reference = true)
	{
		global $ilias,$lng,$tree;

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->type = "grp";
		$this->ilObject($a_id,$a_call_by_reference);

		if($a_call_by_reference)
		{
			$this->object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
			$this->m_grpId = $a_id;
		
		}
		else
		{
			$this->m_grpId = $a_id;		
		}

		$this->tree = $tree;

	}

	/**
	* join Group
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function joinGroup($a_userId, $a_memStatus)
	{
		global $rbacadmin, $rbacsystem;

		//get default group roles (member, admin)
		$grp_DefaultRoles = $this->getDefaultGroupRoles();

		if(isset($a_userId) && isset($a_memStatus))
		{
			if( $rbacsystem->checkAccess("join", $this->getRefId(),'grp') )
			{
				//assignUser needs to be renamed into assignObject
				if(strcmp($a_memStatus,"member") == 0)		//member
				{
					$rbacadmin->assignUser($grp_DefaultRoles["grp_member_role"],$a_userId, false);
				}
				else if(strcmp($a_memStatus,"admin") == 0)		//admin
				{
					$rbacadmin->assignUser($grp_DefaultRoles["grp_admin_role"],$a_userId, true);
				}
				else											//request??
				{
					//todo: check if request role exists
					if(isRole($this->m_roleRequest))
					{

					}

				}
			}
			else
			{
				$this->ilias->raiseError("No permission to join this group",$this->ilias->error_obj->WARNING);
			}

		}
	}
	

	/**
	* leave Group
	* @access	public
	* @param	integer	user-Id
	* @param	integer group-Id
	*/
	function leaveGroup($a_userId, $a_grpId="")
	{

		global $rbacadmin, $rbacsystem, $rbacreview;
		//get rolefolder of group

		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
		//get roles out of folder
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);
		if(count($role_arr) <= 1)
		{
			//echo "You are the last member of this group.<br>";
			return false;
		}
		else
		foreach ($role_arr as $role_id)
		{
			foreach ($rbacreview->assignedUsers($role_id) as $member_id)
			{
				if($member_id == $a_userId)
				{
					$rbacadmin->deassignUser($role_id, $member_id);
				}
			}
		}
		return true;
	}

	/**
	* get group Members
	* @access	public
	* @param	integer	group id
	* @param	return array of groupmembers that are assigned to the groupspecific roles (grp_member,grp_admin)
	*/
	function getGroupMemberIds($a_grpId="")
	{

		global $rbacadmin, $rbacreview;

		if(!empty($a_grpId) )
			$this->m_grpId = $a_grpId;
		else
			$this->m_grpId = $this->object->getRefId();
		
		$usr_arr= array();
		$rolf = $rbacreview->getRoleFolderOfObject($this->m_grpId);
		$rol  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		//interims solution
		//TODO: global roles should be located by a function
		$arr_globalRoles = array(2,3,4,5);
		foreach ($rol as $value)
		{
			if(!in_array($value,$arr_globalRoles))
				foreach ($rbacreview->assignedUsers($value) as $member_id)
				{
					array_push($usr_arr,$member_id);
				}
		}
		$mem_arr = array_unique($usr_arr);
		//var_dump ($mem_arr);
		return $mem_arr;
	}

	function getGroupRole($a_user_id, $a_grp_id="")
	{
		global $rbacadmin, $rbacreview;
		if(isset($a_grp_id))
			$grp_id = a_grp_id;
		else
			$grp_id = $this->m_grpId;

		//$rolf 	   = $rbacreview->getRoleFolderOfObject($this->m_grpId);
		$rolf 	   = $rbacreview->getRoleFolderOfObject($grp_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		//TOOODOOOO: schoen machen !!!
		foreach ($role_arr as $role_id)
		{
			foreach ($rbacreview->assignedUsers($role_id) as $member_id)
			{
				if($member_id == $a_userId)
				{
					$newObj = new Object($role_id, false);
					return $newObj->getTitle();
				}
			}
		}
		return NULL;

	}
	/**
	* delete Group
	* @access	public
	* @param	integer	group id
	*/
	function deleteGroup($a_grpId="")
	{
	}
	
	/**
	* get default group roles
	* @access	public
	* @param 	returns the obj_ids of group specific roles(member,admin)
	*/
	function getDefaultGroupRoles()
	{
		global $rbacadmin, $rbacreview;


		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->getRefId());
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

			if(strcmp($role_Obj->getTitle(), "grp_Member") == 0 )
				$arr_grpDefaultRoles["grp_member_role"] = $role_Obj->getId();

			if(strcmp($role_Obj->getTitle(), "grp_Administrator") == 0 )
				$arr_grpDefaultRoles["grp_admin_role"] = $role_Obj->getId();
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
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='grp_Status_closed'";
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
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='grp_Status_open'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row["obj_id"];
	}

	/**
	* set group status
	* @access	public
	* @param	integer	group id (optional)
	* @param	integer group status (0=public|1=private|2=closed)
	*/
	function setGroupStatus($a_grpStatus)
	{
		global $rbacadmin, $rbacreview;
		
		$grp_Status = array("group_status_public" => 0,"group_status_private" => 1, "group_status_closed" => 2);

		//get Rolefolder of group
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());
		//the (global)roles who must be considered when inheritance is stopped
		//todo: query that fetches the considering roles
		$arr_globalRoles = array(2,3,4,5); //admin,author,learner,guest

	  	if(strcmp($a_grpStatus,"group_status_public") == 0 || strcmp($a_grpStatus,"group_status_private") == 0)			//group status set opened
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'grp_status_closed'
			$arr_ops = $rbacreview->getOperationsOfRole($this->getGrpStatusOpenTemplateId(), 'grp', 8);
			foreach ($arr_globalRoles as $globalRole)
			{
				if($this->getGroupStatus() != NULL)
					$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);

				//revoke all permission on current group object for all(!) global roles, may be a workaround
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);//refid des objektes,dass rechte aberkannt werden, opti.:roleid, wenn nur dieser rechte aberkannt...

				$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());//rollenid,operationen,refid des objektes auf das rechte gesetzt werden

				//copy permissiondefinitions of template for adminrole to localrolefolder of group
				$rbacadmin->copyRolePermission($this->getGrpStatusOpenTemplateId(),8,$globalRole,$rolf_data["child"]);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte übernehmen soll
				//the assignment stops the inheritation
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],$rolf_data["parent"],'n');
			}//END foreach

			$this->m_grpStatus = 2;
		}

		if(strcmp($a_grpStatus,"group_status_private") == 0)			//group status set private (=1)
		{
			$this->m_grpStatus = 1;
		}
  		
		if(strcmp($a_grpStatus,"group_status_closed") == 0)			//group status set closed (=2)
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'grp_status_closed'
			$arr_ops = $rbacreview->getOperationsOfRole($this->getGrpStatusClosedTemplateId(), 'grp', 8);
			foreach ($arr_globalRoles as $globalRole)
			{
				if($this->getGroupStatus() != NULL)
					$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);

				//revoke all permission on current group object for all(!) global roles, may be a workaround
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);//refid des grpobjektes,dass rechte aberkannt werden, opti.:roleid, wenn nur dieser rechte aberkannt...
				//set permissions of global role (admin,author,guest,learner) for group object
				$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());//rollenid,operationen,refid des objektes auf das rechte gesetzt werden

				//copy permissiondefinitions of template for adminrole to localrolefolder of group
				$rbacadmin->copyRolePermission($this->getGrpStatusClosedTemplateId(),8,$globalRole,$rolf_data["child"]);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte übernehmen soll
				//the assignment stops the inheritation
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],$rolf_data["parent"],'n');
			}//END foreach

			$this->m_grpStatus = 0;
		}


		$sql_query1 = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res		= $this->ilias->db->query($sql_query1);

		if($res->numRows() == 0)
			$sql_query = "INSERT INTO grp_data (grp_id, status) VALUES (".$this->getId().",".$grp_Status[$a_grpStatus].")";
		else
			$sql_query = "UPDATE grp_data SET status='".$grp_Status[$a_grpStatus]."' WHERE grp_id='".$this->getId()."'";

		$res = $this->ilias->db->query($sql_query);

	}

	/**
	* get group status
	* @access	public
	* @param	integer	group id
	*/
	function getGroupStatus()
	{
		$sql_query = "SELECT status FROM grp_data WHERE grp_id=".$this->getId();
		$res = $this->ilias->db->query($sql_query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		if($res->numRows() == 0)
		 	return NULL;
		else
			return $row["status"];
	}


	/**
	* create Group Role
	* @access	public
	* @param	integer	role folder id (reference)
	* @param	integer status of group (0=public|1=private|2=closed)
	*/
	function createGroupRoles($rolfId)
	{
		include_once("./classes/class.ilObjRole.php");

		global $rbacadmin;

		// create new role objects
		if (isset($rolfId))
		{
			//set permissions for MEMBER ROLE
			$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='grp_Member_rolt' AND description='Member role template of groups'";
			$res = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);

			//TODO: errorhandling
			if ($res->obj_id)
			{
				// create MEMBER role
				$roleObj = new ilObjRole();
				$roleObj->setTitle("grp_Member");
				$roleObj->setDescription("automatic generated Group-Memberrole of group ref_no.".$this->getRefId());
				$roleObj->create();

				// put the role into local role folder...
				$rbacadmin->assignRoleToFolder($roleObj->getId(),$rolfId,$this->getRefId(),"y");
		
				// set member role id for group object
				$this->m_roleMemberId = $roleObj->getId();
				$rbacadmin->copyRolePermission($res->obj_id,ROLE_FOLDER_ID,$rolfId,$roleObj->getId());		

				// dump role object & $res
				unset($roleObj);
				unset($res);
			}
			else
			{
				$this->ilias->raiseError("Error! Could not find the needed role template to set role permissions for group member!");
			}

			//$ops = array(2,4,8);	//2=visible, 3=read, 8=leave
			//$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);

			//set permissions for ADMIN ROLE
			$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='grp_Admin_rolt' AND description='Administrator role template of groups'";
			$res = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);
		
			//TODO: errorhandling, if query return more than 1 id matching this query
			if ($res->obj_id)
			{
				// create ADMIN role
				$roleObj = new ilObjRole();
				$roleObj->setTitle("grp_Administrator");
				$roleObj->setDescription("automatic generated Group-Adminrole of group ref_no.".$this->getRefId());
				$roleObj->create();

				// put the role into local role folder...
				$rbacadmin->assignRoleToFolder($roleObj->getId(),$rolfId,$this->getRefId(),"y");
		
				// set adimn role id for group object
				$this->m_roleAdminId = $roleObj->getId();
				$rbacadmin->copyRolePermission($res->obj_id,ROLE_FOLDER_ID,$rolfId,$roleObj->getId());

				// dump role object & $res
				unset($roleObj);
				unset($res);
			}
			else
			{
				$this->ilias->raiseError("Error! Could not find the needed role template to set role permissions for group administrator!");
			}
			//$ops = array(1,2,3,4,6,8);
			//$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);


			/*
			//request-role <=> group is private
			if($this->m_grpStatus == 1)
			{
				$roleObj = new ilObjRole();
				$roleObj->setTitle("grp_Request");
				$roleObj->setDescription("automatic generated Group-Requestrole");
				$roleObj->create();
				$roleObj->createReference();
				$parent_id = $this->->getParentId($_GET["ref_id"]);
				$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');

				$this->m_roleRequestId = $roleObj->getId();
				//set permissions for request-role
				//??? TODO : Do this role need any permissions?
				$ops = array(2);
				$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);

				unset($roleObj);
			}
			*/

			//create permissionsettings for grp_admin and grp_member
			$grp_DefaultRoles = $this->getDefaultGroupRoles();

			$ops = array(2,3,8);
			$rbacadmin->grantPermission($grp_DefaultRoles["grp_member_role"],$ops,$this->getRefId());
			$ops = array(1,2,3,4,5,6,7,8);
			$rbacadmin->grantPermission($grp_DefaultRoles["grp_admin_role"],$ops,$this->getRefId());
		}
	}

	/**
	* get
	* @access	public
	* @param	integer	group id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function getGroupRoleId($a_userId,$grpId="")
	{
		global $rbacadmin, $rbacreview;

		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->m_grpId);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		//TOOODOOOO: schoen machen !!!
		foreach ($role_arr as $role_id)
		{
			foreach ($rbacreview->assignedUsers($role_id) as $member_id)
			{
				if($member_id == $a_userId)
				{
					return $role_id;
				}
			}
		}
		return NULL;		
	}

	
	function getContextPath2($a_endnode_id, $a_startnode_id = 0)
	{
		global $tree;		

		$path = "";		

		$tmpPath = $tree->getPathFull($a_endnode_id, $a_startnode_id);		

		// count -1, to exclude the forum itself
		for ($i = 0; $i < (count($tmpPath) - 1); $i++)
		{
			if ($path != "")
			{
				$path .= " > ";
			}
		
			$path .= $tmpPath[$i]["title"];
		}

		return $path;
	}




	/**
	* get Member status
	* @access	public
	* @param	integer	user id
	*/
	function getContextPath($a_userId="")
	{
	}

	/**
	* set member status
	* @access	public
	* @param	integer	user_id
	* @param	integer role_id
	*/
	function setMemberStatus($a_userId, $a_status)
	{
	}
	
	/**
	* is Member
	* @access	public
	* @param	integer	user_id
	*/
	function isMember($a_userId)
	{
		global $rbacadmin, $rbacreview;

		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->m_grpId);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);
		foreach ($role_arr as $role_id)
		{
			if( in_array($a_userId,$rbacreview->assignedUsers($role_id) ))
				return true;
		}
		return false;

	}

	function createNewGroupTree()
	{

	$this->grp_tree = new ilTree($this->getRefId());
	$this->grp_tree->setTableNames("grp_tree","obj_data");
	$this->grp_tree->addTree($this->getRefId());
	
	}	

	/**
	* copy all prperties and subobjects of a group.
	* Does not copy the settings in the group's local role folder. Instead a new local role folder is created from
	* the template settings (same process as creating a new group manually)
	* attention: frm_data is linked with ILIAS system (object_data) with the obj_id and NOT ref_id! 
	* 
	* @access	public
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{		
		global $rbacadmin;

		$new_ref_id = parent::clone($a_parent_ref);
		
		// get object instance
		$groupObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create role folder and set up default local roles (like in saveObject)
		include_once ("classes/class.ilObjRoleFolder.php");
		$rfoldObj = new ilObjRoleFolder();
		$rfoldObj->setTitle("Local roles");
		$rfoldObj->setDescription("Role Folder of group ref_no.".$groupObj->getRefId());
		$rfoldObj->create();
		$rfoldObj->createReference();
		$rfoldObj->putInTree($groupObj->getRefId());
		$rfoldObj->setPermissions($groupObj->getRefId());

		//the order is very important, please do not change: first create roles and join group, then setGroupStatus !!!
		$groupObj->createGroupRoles($rfoldObj->getRefId());

		//creator becomes admin of group
		$groupObj->joinGroup($groupObj->getOwner(),"admin");
		
		// TODO: function getGroupStatus returns integer but setGroupStatus expects a string.
		// I disabled this function. Please investigate
		// shofmann@databay.de	4.7.03
		// copy group status
		// 0=public,1=private,2=closed
		//$groupObj->setGroupStatus($this->getGroupStatus());
		
		//create new tree in "grp_tree" table; each group has his own tree in "grp_tree" table
		$groupObj->createNewGroupTree();

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
		unset($groupObj);
		unset($rfoldObj);
		unset($roleObj);

		return $new_ref_id;
	}
} //END class.GroupObject
?>
