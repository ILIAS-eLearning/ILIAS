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

		if(isset($a_userId) && isset($a_memStatus))
		{
			if( $rbacsystem->checkAccess("join", $this->getRefId(),'grp') )
			{
				//assignUser needs to be renamed into assignObject
				if(strcmp($a_memStatus,"member") == 0)			//member
				{
					$rbacadmin->assignUser($this->m_roleMemberId,$a_userId, false);
					$ops = array(2,3,7,8);
					$rbacadmin->grantPermission($this->m_roleMemberId,$ops,$this->getRefId());

				}
				else if(strcmp($a_memStatus,"admin") == 0)		//admin
				{
					$rbacadmin->assignUser($this->m_roleAdminId,$a_userId, true);
					$ops = array(5,6,1,7,8,3,2,4);
					$rbacadmin->grantPermission($this->m_roleAdminId,$ops,$this->getRefId());
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
		global $rbacadmin, $rbacsystem;

		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->m_grpId);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			foreach ($rbacreview->assignedUsers($role_id) as $member_id)
			{
				if($rbacsystem->checkAccess("leave",$this->getRefId()) )

				if($member_id == $a_userId)
				{
//					if(strcmp($this->getGroupRole($member_id),"Member"))
//					{
					$rbacadmin->deassignUser($role_id, $member_id);
//					}
				}
			}
		}
	}
	
	/**
	* get group Members
	* @access	public
	* @param	integer	group id
	*/
	function getGroupMemberIds($a_grpId="")
	{
		global $rbacadmin, $rbacreview;

		$this->m_grpId = $a_grpId;

		$usr_arr= array();
		$rolf = $rbacreview->getRoleFolderOfObject($this->m_grpId);
		$rol  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($rol as $value) 
		{	
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

		$rolf 	   = $rbacreview->getRoleFolderOfObject($this->m_grpId);
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
		//todo: query that fetches the considering roles
		$arr_globalRoles = array(2,3,4,5); //admin,author,learner,guest

  		if(strcmp($a_grpStatus,"group_status_closed") == 0)			//group status set closed (=2)
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'grp_status_closed'
			//todo: id of template is hard coded and must should be queried by a function
			$arr_ops = $rbacreview->getOperationsOfRole(102, 'grp', 8);
			foreach ($arr_globalRoles as $globalRole)
			{
				if($this->getGroupStatus() != NULL)
					$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);

				//revoke all permission on current group object for all(!) global roles, may be a workaround
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);//refid des grpobjektes,dass rechte aberkannt werden, opti.:roleid, wenn nur dieser rechte aberkannt...
				//set permissions of global role (admin,author,guest,learner) for group object
				$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());//rollenid,operationen,refid des objektes auf das rechte gesetzt werden

				//copy permissiondefinitions of template for adminrole to localrolefolder of group
				$rbacadmin->copyRolePermission(102,8,$globalRole,$rolf_data["child"]);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte übernehmen soll
				//the assignment stops the inheritation
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],$rolf_data["parent"],'n');
			}//END foreach

			$this->m_grpStatus = 0;
		}

		if(strcmp($a_grpStatus,"group_status_private") == 0)			//group status set private (=1)
		{
			$this->m_grpStatus = 1;
		}

	  	if(strcmp($a_grpStatus,"group_status_public") == 0)			//group status set opened
		{
			//get defined operations on object group depending on group status "CLOSED"->template 'grp_status_closed'
			//todo: id of template is hard coded and must should be queried by a function
			$arr_ops = $rbacreview->getOperationsOfRole(103, 'grp', 8);
			foreach ($arr_globalRoles as $globalRole)
			{
				if($this->getGroupStatus() != NULL)
					$rbacadmin->deleteLocalRole($globalRole,$rolf_data["child"]);

				//revoke all permission on current group object for all(!) global roles, may be a workaround
				$rbacadmin->revokePermission($this->getRefId(), $globalRole);//refid des objektes,dass rechte aberkannt werden, opti.:roleid, wenn nur dieser rechte aberkannt...

				$rbacadmin->grantPermission($globalRole,$arr_ops, $this->getRefId());//rollenid,operationen,refid des objektes auf das rechte gesetzt werden

				//copy permissiondefinitions of template for adminrole to localrolefolder of group
				$rbacadmin->copyRolePermission(102,8,$globalRole,$rolf_data["child"]);			//RollenTemplateId, Rollenfolder von Template (->8),RollenfolderRefId von Gruppe,Rolle die Rechte übernehmen soll
				//the assignment stops the inheritation
				$rbacadmin->assignRoleToFolder($globalRole,$rolf_data["child"],$rolf_data["parent"],'n');
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
		require_once("./classes/class.ilObjRole.php");
		global $rbacadmin;


		// create new role objects
		if(isset($rolfId))
		{

			//member-role
			$roleObj = new ilObjRole();
			$roleObj->setTitle("grp_Member");
			$roleObj->setDescription("automatic generated Group-Memberrole");
			$roleObj->create();
			$roleObj->createReference();
			$parent_id = $this->getRefId();
			$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');

			$this->m_roleMemberId = $roleObj->getId();

			//set permissions for member-role

			$rbacadmin->copyRolePermission(101,8, $rolfId,$roleObj->getId()  );

			//$ops = array(2,4,8);	//2=visible, 3=read, 8=leave
			//$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);

			unset($roleObj);

			//admin-role
			$roleObj = new ilObjRole();
			$roleObj->setTitle("grp_Administrator");
			$roleObj->setDescription("automatic generated Group-Adminrole");
			$roleObj->create();
			$roleObj->createReference();
			$parent_id = $this->getRefId();
			$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');

			$this->m_roleAdminId = $roleObj->getId();

			//set permissions for admin-role
			$rbacadmin->copyRolePermission(100,8, $rolfId, $roleObj->getId() );

			//$ops = array(1,2,3,4,6,8);
			//$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);

			unset($roleObj);

			//request-role <=> group is private
			if($this->m_grpStatus == 1)
			{
				$roleObj = new ilObjRole();
				$roleObj->setTitle("grp_Request");
				$roleObj->setDescription("automatic generated Group-Requestrole");
				$roleObj->create();
				$roleObj->createReference();
				$parent_id = $this->tree->getParentId($_GET["ref_id"]);
				$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');

				$this->m_roleRequestId = $roleObj->getId();
				//set permissions for request-role
				//??? TODO : Do this role need any permissions?
				$ops = array(2);
				$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);

				unset($roleObj);
			}

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
	}
	

} //END class.GroupObject
?>
