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
	* join Group
	* @access	public
	* @param	integer	user id of new member
	* @param	integer	member status [0=member|1=admin]
	*/
	function join($a_user_id, $a_memStatus)
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
				
				ilObjUser::updateActiveRoles($a_user_id);

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
	function leave($a_user_id, $a_grpId="")
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
		//$arr_globalRoles = array(2,3,4,5); //admin,author,learner,guest
		$arr_globalRoles = array_diff(array_keys($rbacreview->getParentRoleIds($this->getRefId())),$rbacreview->getRolesOfRoleFolder($rolf_data["ref_id"]));

		//var_dump("<pre>",$rolf_data,"</pre>");exit;
		
	  	if ($a_grpStatus == 0 || $a_grpStatus == 1)
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'il_grp_status_closed'
			$arr_ops = $rbacreview->getOperationsOfRole($this->getGrpStatusOpenTemplateId(), 'grp', 8);

			foreach ($arr_globalRoles as $globalRole)
			{
				if($this->getGroupStatus() != NULL)
					$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);

				//revoke all permission on current group object for all(!) global roles, may be a workaround
				//refid des objektes,dass rechte aberkannt werden, opti.:roleid, wenn nur dieser rechte aberkannt...
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);

				//rollenid,operationen,refid des objektes auf das rechte gesetzt werden
				$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());

				//copy permissiondefinitions of template for adminrole to localrolefolder of group
				//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte Ã¼bernehmen soll
				$rbacadmin->copyRolePermission($this->getGrpStatusOpenTemplateId(),8,$rolf_data["child"],$globalRole);

				//the assignment stops the inheritation
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],"n");
			}//END foreach

			$this->m_grpStatus = 0;
		}

	  	if($a_grpStatus == 1)
		{
			$this->m_grpStatus = 1;
		}

	  	if($a_grpStatus == 2)
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'il_grp_status_closed'
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
				$rbacadmin->copyRolePermission($this->getGrpStatusClosedTemplateId(),8,$globalRole,$rolf_data["child"]);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte Ã¼bernehmen soll
				//the assignment stops the inheritation
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],"false");
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
			$this->leave($a_user_id);
			$this->join($a_user_id,$a_member_status);
		}
	}


	/**
	* create Group Role
	* @access	public
	* @param	integer	role folder id (reference)
	*/
	function createDefaultGroupRoles($rolfId)
	{
		include_once("./classes/class.ilObjRole.php");

		global $rbacadmin;

		// create new role objects
		if (isset($rolfId))
		{
			//set permissions for MEMBER ROLE
			$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_member'";
			$res = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);

			//TODO: errorhandling
			if ($res->obj_id)
			{
				// create MEMBER role
				$roleObj = new ilObjRole();
				$roleObj->setTitle("il_grp_member_".$this->getRefId());
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
			$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_admin'";
			$res = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);

			//TODO: errorhandling, if query return more than 1 id matching this query
			if ($res->obj_id)
			{
				// create ADMIN role
				$roleObj = new ilObjRole();
				$roleObj->setTitle("il_grp_admin_".$this->getRefId());
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
			//$ops = array(1,2,3,4,6,8);$this->getRefId()
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

	function createNewGroupTree($objGrpId,$objGrpRefId)
	{
		
		//echo "grp_obj_id".$objGrpId."-grp_ref_id".$objGrpRefId;
		$grp_tree = new ilTree($objGrpId);
		$grp_tree->setTableNames("grp_tree","object_data","object_reference");
		$grp_tree->addTree($objGrpId,$objGrpRefId);
		
		$q1 = "UPDATE grp_tree SET perm=1 WHERE parent=0 AND child=".$objGrpRefId." AND obj_id IS NULL";
		$this->ilias->db->query($q1);
		
		$q2 = "UPDATE grp_tree SET obj_id=".$objGrpId." WHERE parent=0 AND child=".$objGrpRefId." AND obj_id IS NULL";
		$this->ilias->db->query($q2);
			
		
		//var_dump($this->grp_tree);
		//return $grp_tree;
	}
	
	/*
	*
	*@param integer ref_id of the new object
	*@param integer ref_id of of the parent node
	+@param integer obj_id of the group (tree_id)
	*@param integer obj_id of the new object
	*/
	function insertGroupNode($new_node_ref_id,$parent_ref_id,$grp_tree_id,$new_node_obj_id=-1 )
	{	//echo $new_node_obj_id."-".$parent_obj_id."-".$grp_tree_id."-".$new_node_ref_id;
		$grp_tree = new ilTree($grp_tree_id);
		$grp_tree->setTableNames("grp_tree","object_data");
		
		//todo berprfen ob eintrag schon existiert
		
		$grp_tree->insertNode($new_node_ref_id,$parent_ref_id);
		
		if(isset($new_node_obj_id) && $new_node_obj_id > 0)
		{echo "ref_da";
			$q1 = "UPDATE grp_tree SET obj_id=".$new_node_obj_id." WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
			$this->ilias->db->query($q1);
			
			$q2 = "UPDATE grp_tree SET perm=1 WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
			$this->ilias->db->query($q2);
		}
		else
		{	
			$q1 = "UPDATE grp_tree SET obj_id=-1 WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
			$this->ilias->db->query($q1);
		
			$q2 = "UPDATE grp_tree SET perm=0 WHERE parent=".$parent_ref_id." AND child=".$new_node_ref_id;
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
		// TODO: MUST BE REPLACED BY initDefaultRoles()!!!! title and description are set wrong!!
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
		$groupObj->join($groupObj->getOwner(),"admin");
		
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
		
		$query = "DELETE FROM grp_tree WHERE tree=".$this->getId();
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
	function initRoleFolder()
	{
		global $rbacadmin;
		
		// create a local role folder
		$rfoldObj = $this->createRoleFolder();

		return $rfoldObj;
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
		$grp_tree = new ilGroupTree($this->getId());
		
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
		$grp_tree = new ilGroupTree($this->getId());
		$this->insertGroupNode($a_source_id,$a_dest_id,$this->getId(),(int)$a_obj_id);
		
		$saved_tree = new ilGroupTree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->insertSavedNodesInGrpTree($child["child"],$a_source_id,$a_tree_id,$a_obj_id);
		}
	}
	
	/**
	* updates the Group trees
	+
	*@access  public
	*@param	 integer	reference id of object where the event occured	
	*/
	function pasteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		
		$grp_tree = new ilTree($this->getId());
		$grp_tree->setTableNames("grp_tree","object_data");
	
		if($_GET["parent_non_rbac_id"] > 0)
		{  
			foreach ($a_params as $parameter => $value)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($value);
				$this->insertGroupNode($new_node->getRefId(),$_GET["parent_non_rbac_id"],$this->getId(),$new_node->getId());
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
					$this->insertGroupNode($child["ref_id"],$object->getRefId(),$this->getId(),$child["obj_id"]);

					//repeat the procedure one level deeper			
					$this->pasteGrpTree($child["ref_id"],$a_parent_non_rbac_id,$a_params);
				}
			}
		}
	}
	
	function cutGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{	
		global $tree;

		$tmp_object =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);
		$obj_ref_id = $tmp_object->getRefId();
		$obj_type = $tmp_object->getType();
		
		while ($obj_type != "grp")
		{
			$obj_ref_id = $tree->getParentId($obj_ref_id);
			$tmp_object =& $this->ilias->obj_factory->getInstanceByRefId($obj_ref_id);
			$obj_type = $tmp_object->getType();
		
		}
		
		$old_grp_tree = new ilTree($tmp_object->getId());
		$old_grp_tree->setTableNames("grp_tree","object_data");
		
		if ($a_parent_non_rbac_id > 0)
		{
			foreach ( $a_params as $parameter => $value)
			{
				$tmp_object =& $this->ilias->obj_factory->getInstanceByRefId($value);
				$note_data = $old_grp_tree->getNodeData($tmp_object->getRefId());
				$old_grp_tree->deleteTree($note_data);
			}
		}
		else
		{
			foreach ($a_params as $parameter => $value)
			{
				$tmp_object =& $this->ilias->obj_factory->getInstanceByRefId($value);
				$note_data = $old_grp_tree->getNodeData($tmp_object->getRefId());
				$old_grp_tree->deleteTree($note_data);
			}
		}		
	}

	function linkGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{
		$grp_tree = new ilTree($this->getId());
		$grp_tree->setTableNames("grp_tree","object_data");
		
		if ($_GET["parent_non_rbac_id"] > 0)
		{
			foreach ($a_params as $parameter)
			{ 
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
				$this->insertGroupNode($new_node->getRefId(),$_GET["parent_non_rbac_id"],$this->getId(),$new_node->getId());
			}
		}

		else
		{
			foreach ($a_params as $parameter)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
				$this->insertGroupNode($new_node->getRefId(),$this->getRefId(),$this->getId(),$new_node->getId());
			}
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
	}
	
	
	function newGrpTree($a_ref_id,$a_parent_non_rbac_id)
	{  
//var_dump($this->tree);
//exit;
		$grp_tree = new ilTree($this->getId());
		$grp_tree->setTableNames("grp_tree","object_data");
		
		//if  grp object not in grp_table first put it in
		if(!$grp_tree->isInTree($this->getRefId()))
		{
			$this->createNewGroupTree($this->getId(),$this->getRefId());
		}
		//else condition will be executed only if the Object already exists in the grp_tree table
		else
		{ 
			if ($a_parent_non_rbac_id > 0)
			{	 
				$childrenNodes = $this->tree->getChilds($this->getRefId()); 

				foreach ($childrenNodes as $child)
				{
					if (!$this->getRefId() == $grp_tree->getParentId($child["ref_id"]))
					{	
						$this->insertGroupNode($child["ref_id"],$a_parent_non_rbac_id,$this->getId(),$child["obj_id"]);
						
					}
				}
				
			}
			else
			{ 
				$childrenNodes = $this->tree->getChilds($_GET["ref_id"]); 

				foreach ( $childrenNodes as $child)
				{
					$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
					//echo "ref_id:".$a_ref_id;
					if( !$object->getRefId()==$grp_tree->getParentId($child["ref_id"]) )
					{	
						$this->insertGroupNode($child["ref_id"],$object->getRefId(),$this->getId(),$child["obj_id"]);
						//echo $child["obj_id"]."-".$object->getId()."-".$this->getId()."-".$child["ref_id"];
					}
				}
			}
		}
	}
	
	function copyGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params)
	{	
		//echo $a_ref_id."-".$a_parent_non_rbac_id; var_dump($a_params);
		
		$grp_tree = new ilTree($this->getId());
		$grp_tree->setTableNames("grp_tree","object_data");
		
		if ($_GET["parent_non_rbac_id"] > 0)
		{
			foreach ($a_params as $parameter)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
				$this->insertGroupNode($new_node->getRefId(),$_GET["parent_non_rbac_id"],$this->getId(),$new_node->getId());
			}
		}
		else
		{	
			foreach ($a_params as $parameter)
			{
				$new_node =& $this->ilias->obj_factory->getInstanceByRefId($parameter);
				$this->insertGroupNode($new_node->getRefId(),$this->getRefId(),$this->getId(),$new_node->getId());
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
		$grp_tree = new ilGroupTree($this->getId());
		//$grp_tree->setTableNames("grp_tree","object_data");

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
		$grp_tree = new ilGroupTree($this->getId());
		
		// DELETE THEM
		foreach ($_POST["trash_id"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			
			$tmp_obj=& $this->ilias->obj_factory->getInstanceByRefId($id);
			$saved_tree = new ilGroupTree(-(int)$tmp_obj->getId());
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
			$dest_obj=& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);
			
			// INSERT 
			$this->insertSavedNodesInGrpTree($tmp_obj->getRefId(),$dest_obj->getRefId(),-(int) $tmp_obj->getId(),$id);
			
			// DELETE SAVED TREE
			$saved_tree = new ilGroupTree(-(int)$tmp_obj->getId());
			$saved_tree->deleteTree($saved_tree->getNodeData($tmp_obj->getRefId()));
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
	{  //echo " notify ref".$a_ref_id."node".$a_node_id;
		// object specific event handling
		global $tree;
		
		switch ($a_event)
		{
			case "undelete":
				$this->undeleteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Group ".$this->getRefId()." triggered by undelete event. Objects are gotten back from trash at target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "removeFromSystem":
				$this->removeFromSystemGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Group ".$this->getRefId()." triggered by removeFromSystem event. Objects are removed from System at target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "confirmedDelete":
				$this->confirmedDeleteGrpTree($a_ref_id,$a_parent_non_rbac_id,$a_params);
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Group ".$this->getRefId()." triggered by confirmedDelete event. Objects put in trash at target object ref_id: ".$a_ref_id;
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
				//echo "Group ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;				
				$this->newGrpTree($a_ref_id,$a_parent_non_rbac_id);
				
				//echo "Group ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"]) 
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();

			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}

		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}
} //END class.ilObjGroup
?>
