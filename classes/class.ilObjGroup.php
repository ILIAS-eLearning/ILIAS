<?php
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

		$this->m_grpId = $a_id;
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
		global $rbacadmin;
		
		if(isset($a_userId))
		{
			//assignUser needs to be renamed into assignObject
			if(strcmp($a_memStatus,"member") == 0)			//member
			{
				$rbacadmin->assignUser($this->m_roleMemberId,$a_userId, false);			
			}
			else if(strcmp($a_memStatus,"admin") == 0)		//admin
			{
				$rbacadmin->assignUser($this->m_roleAdminId,$a_userId, false);				
			}
			else											//request??
			{
				//todo: check if request role exists
				if(isRole($this->m_roleRequest))
				{
				
				}
				
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
	{}
	
	/**
	* get group Members
	* @access	public
	* @param	integer	group id
	*/
	function getGroupMembers($a_grpId="")
	{}
	
	/**
	* delete Group
	* @access	public
	* @param	integer	group id
	*/
	function deleteGroup($a_grpId="")
	{}
	
	/**
	* set group status
	* @access	public
	* @param	integer	group id (optional)
	* @param	integer group status (0=public|1=private|2=closed)
	*/
	function setGroupStatus($a_grpStatus)
	{
		$this->m_grpStatus = $a_grpStatus;
		
		$sql_query = "INSERT INTO grp_data (grp_id, status) VALUES (".$this->m_grpId.",".$a_grpStatus.")";
		$res = $this->ilias->db->query($sql_query);

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
			$roleObj->setTitle("Member");
			$roleObj->setDescription("automatic generated Group-Memberrole");
			$roleObj->create();
			$roleObj->createReference();
			$parent_id = $this->tree->getParentId($_GET["ref_id"]);
			$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');

			$this->m_roleMemberId = $roleObj->getId();
			
			//set permissions for member-role
			$ops = array(2,4,8);	//2=visible, 3=read, 8=leave
			$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);
			unset($roleObj);

			//admin-role
			$roleObj = new ilObjRole();
			$roleObj->setTitle("Administrator");
			$roleObj->setDescription("automatic generated Group-Adminrole");
			$roleObj->create();
			$roleObj->createReference();			
			$parent_id = $this->tree->getParentId($_GET["ref_id"]);
			$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');
			
			$this->m_roleAdminId = $roleObj->getId();

			//set permissions for admin-role
			$ops = array(1,2,3,4,6,8);
			$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);
			
			unset($roleObj);			

			//request-role <=> group is private
			if($this->m_grpStatus == 1)
			{
				$roleObj = new ilObjRole();
				$roleObj->setTitle("Request");
				$roleObj->setDescription("automatic generated Group-Requestrole");
				$roleObj->create();
				$roleObj->createReference();				
				$parent_id = $this->tree->getParentId($_GET["ref_id"]);
				$rbacadmin->assignRoleToFolder($roleObj->getId(), $rolfId, $parent_id,'y');

				$this->m_roleRequestId = $roleObj->getId();	
				//set permissions for request-role
				$ops = array(2);
				$rbacadmin->setRolePermission($roleObj->getId(),"grp",$ops,$rolfId);
				
				unset($roleObj);			
			}

		}
	}	
	/**
	* get group status
	* @access	public
	* @param	integer	group id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function getGroupStatus($a_grpId="")
	{
	}

	/**
	* get Member status
	* @access	public
	* @param	integer	user id
	*/
	function getMemberStatus($a_userId="")
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
