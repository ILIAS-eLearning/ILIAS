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
	var $ref_grpId;
	
	var $obj_grpId;

	var $m_grpStatus;

	var $ilias;

	var $tree;
	
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
		global $ilias,$lng,$tree;

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->type = "grp";
		$this->ilObject($a_id,$a_call_by_reference);

		if($a_call_by_reference)
		{
			$this->object =& $this->ilias->obj_factory->getInstanceByRefId($a_id);
			
			$this->ref_grpId = $a_id;
			
			
		}
		else
		{
			$this->obj_grpId = $a_id;		
		}

		$this->tree = $tree;

	}

	/**
	* join Group
	* @access	public
	* @param	integer	user id of new member
	* @param	integer	member status [0=member|1=admin]
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function joinGroup($a_user_id, $a_memStatus)
	{
		global $rbacadmin, $rbacsystem;

		//get default group roles (member, admin)
		$grp_DefaultRoles = $this->getDefaultGroupRoles();

		if(isset($a_user_id) && isset($a_memStatus) && !$this->isMember($a_user_id) )
		{
			if( $rbacsystem->checkAccess("join", $this->getRefId(),'grp') )
			{
				//assignUser needs to be renamed into assignObject
				if( (strcmp($a_memStatus,"member") == 0) || $a_memStatus == 0)		//member
				{
					$rbacadmin->assignUser($grp_DefaultRoles["grp_member_role"],$a_user_id, false);
				}
				else if( (strcmp($a_memStatus,"admin") == 0) || $a_memStatus == 1)		//admin
				{
					$rbacadmin->assignUser($grp_DefaultRoles["grp_admin_role"],$a_user_id, true);
				}

				return true;
			}
			else
			{
				$this->ilias->raiseError("No permission to join this group",$this->ilias->error_obj->WARNING);

			}
		}

		return false;
	}


	/**
	* leave Group
	* @access	public
	* @param	integer	user-Id
	* @param	integer group-Id
	*/
	function leaveGroup($a_user_id, $a_grpId="")
	{
		global $rbacadmin, $rbacsystem, $rbacreview;

		$arr_members = array();
		
		if(isset($a_grp_id))
			$grp_id = $a_grp_id;
		else
			$grp_id = $this->getRefId();

		$grp_DefaultRoles = $this->getDefaultGroupRoles($grp_id);

		foreach($grp_DefaultRoles as $role_id)
		{
			$grp_assignedUsers = $rbacreview->assignedUsers($role_id);
			foreach($grp_assignedUsers as $user)
				array_push($arr_members, $user);
		}
		if(count($arr_members) <= 1 || !in_array($a_user_id, $arr_members))
			return false;
		else
		{
			$rbacadmin->deassignUser($this->getGroupRoleId($a_user_id), $a_user_id);
				return true;
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
		$rol  = $this->getDefaultGroupRoles($grp_id);

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
		foreach ($rbacreview->assignedUsers($roles["grp_admin_role"]) as $member_id)
		{
			array_push($usr_arr,$member_id);
		}

		return $usr_arr;
	}

	/**
	* delete Group
	* @access	public
	* @param	integer	group id
	*/
	function deleteGroup($a_grpId="")
	{
		//TO DO!
	}

	/**
	* get default group roles
	* @access	public
	* @param 	returns the obj_ids of group specific roles(member,admin)
	*/
	function getDefaultGroupRoles($a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		if(strlen($a_grp_id) > 0)
			$grp_id = $a_grp_id;
		else
			$grp_id = $this->getRefId();

		//$rolf 	   = $rbacreview->getRoleFolderOfObject($this->getRefId());
		$rolf 	   = $rbacreview->getRoleFolderOfObject($grp_id);
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
	* get group member status
	* @access	public
	* @param	returns [0=grp_member_role|1=grp_admin_role]
	*/
	function getMemberStatus($a_user_id, $a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		if(strlen($a_grp_id) > 0)
			$grp_id = $a_grp_id;
		else
			$grp_id = $this->getRefId();

		$roles = $this->getDefaultGroupRoles($grp_id);

		if( in_array($a_user_id,$rbacreview->assignedUsers($roles["grp_member_role"]) ))
			return 0;		//MEMBER
		if( in_array($a_user_id,$rbacreview->assignedUsers($roles["grp_admin_role"]) ))
			return 1;		//ADMIN

	}

	/**
	* set member status
	* @access	public
	* @param	integer	user id
	* @param	integer member status (0=member|1=admin)
	*/
	function setMemberStatus($a_user_id, $a_member_status)
	{
		if(isset($a_user_id) && isset($a_member_status))
		{
			$this->leaveGroup($a_user_id);
			$this->joinGroup($a_user_id,$a_member_status);
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
		global $rbacadmin, $rbacreview;

		//get Rolefolder of group
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());
		//the (global)roles who must be considered when inheritance is stopped
		//todo: query that fetches the considering global roles
		$arr_globalRoles = array(2,3,4,5); //admin,author,learner,guest

	  	if($a_grpStatus == 0 || $a_grpStatus == 1)
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
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],'n');
			}//END foreach

			$this->m_grpStatus = 0;
		}

	  	if($a_grpStatus == 1)
		{
			$this->m_grpStatus = 1;
		}

	  	if($a_grpStatus == 2)
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
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],'n');
			}//END foreach

			$this->m_grpStatus = 2;
		}


		$sql_query1 = "SELECT * FROM grp_data WHERE grp_id='".$this->getId()."'";
		$res		= $this->ilias->db->query($sql_query1);

		if($res->numRows() == 0)
			$sql_query = "INSERT INTO grp_data (grp_id, status) VALUES (".$this->getId().",".$this->m_grpStatus.")";
		else
			$sql_query = "UPDATE grp_data SET status='".$this->m_grpStatus."' WHERE grp_id='".$this->getId()."'";

		$res = $this->ilias->db->query($sql_query);

	}

	/**
	* get group status
	* @access	public
	* @param	return group status[0=public|1=?private?|2=closed]
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
				$rbacadmin->assignRoleToFolder($roleObj->getId(),$rolfId,"y");

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
				$rbacadmin->assignRoleToFolder($roleObj->getId(),$rolfId,"y");

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
				$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, 'y');

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
	* get Group Role
	* @access	public
	* @param	return the id of the group role user is assigned to (grp_Member, grp_Admin)
	*/
	function getGroupRoleId($a_user_id, $a_grp_id="")
	{
		global $rbacadmin, $rbacreview;


		if(strlen($a_grp_id) > 0)
			$grp_id = $a_grp_id;
		else
			$grp_id = $this->getRefId();

		$grp_Roles = $this->getDefaultGroupRoles($grp_id);

		foreach ($grp_Roles as $role_id)
		{
			if( in_array($a_user_id,$rbacreview->assignedUsers($role_id) ))
			{
				return $role_id;
			}
		}
		return NULL;
	}

	/**
	* get
	* @access	public
	* @param	integer	group id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
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
	* is Member
	* @access	public
	* @param	integer	user_id
	*/
	function isMember($a_userId)
	{
		global $rbacadmin, $rbacreview;

		$grp_Roles = $this->getDefaultGroupRoles();

		foreach ($grp_Roles as $role_id)
		{
			if( in_array($a_userId,$rbacreview->assignedUsers($role_id) ))
				return true;
		}
		return false;

	}

	function createNewGroupTree()
	{
		$this->grp_tree = new ilTree($this->object->getId());
		$this->grp_tree->setTableNames("grp_tree","object_data","object_reference");
		$this->grp_tree->addTree($this->object->getId());
		//var_dump($this->grp_tree);
		//return $this->grp_tree;
	}
	
	/*
	*
	*@param integer obj_id of the new object
	*@param integer ref_id of the new object
	*@param integer obj_id of the group;root/tree id of the group
	*/
	function insertGroupNode($new_node_obj_id,$new_node_ref_id, $parent_obj_id )
	{	
		$this->grp_tree->insertNode($new_node_obj_id,$parent_obj_id);
		
		if(isset($new_node_ref_id) && $new_node_ref_id>0)
		{
			$q1 = "UPDATE grp_tree SET ref_id=".$new_node_ref_id." WHERE parent=".$parent_obj_id." AND child=".$new_node_obj_id;
			$this->ilias->db->query($q1);
			
			$q2 = "UPDATE grp_tree SET perm=1 WHERE parent=".$parent_obj_id." AND child=".$new_node_obj_id;
			$this->ilias->db->query($q2);
		}
		else
		{
			$q2 = "UPDATE grp_tree SET perm=0 WHERE parent=".$parent_obj_id." AND child=".$new_node_obj_id;
			$this->ilias->db->query($q2);
		}
		
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
		
		// put here group specific stuff
		
		return true;
	}

} //END class.ilObjGroup
?>
