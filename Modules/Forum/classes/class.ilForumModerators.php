<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilForumModerators
*
* @author Nadia Ahmad <nahmad@databay.de>
*
* @ingroup ModulesForum
*/
class ilForumModerators
{
	private $db = null;
	private $ref_id = 0;
	
	public function __construct($a_ref_id)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		$this->ref_id = $a_ref_id;
	}

	/**
	 * @param int $ref_id
	 */
	public function setRefId($ref_id)
	{
		$this->ref_id = $ref_id;
	}

	/**
	 * @return int
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	public function addModeratorRole($a_usr_id)
	{
		global $rbacreview, $rbacadmin;
		
		$role_list = $rbacreview->getRoleListByObject($this->getRefId());
		foreach ($role_list as $role)
		{
			if(strpos($role['title'], 'il_frm_moderator') !== false)
			{
				$a_rol_id = $role['obj_id'];
				break;
			}
		}
		
		if((int)$a_rol_id)
		{		
			$user = $rbacadmin->assignUser($a_rol_id, $a_usr_id);
			return true;
		}		
	
		return false;	
	}
	
	public function detachModeratorRole($a_usr_id)
	{
		global $rbacreview, $rbacadmin;
		
		$role_list = $rbacreview->getRoleListByObject($this->getRefId());
		foreach ($role_list as $role)
		{
			if(strpos($role['title'], 'il_frm_moderator') !== false)
			{
				$a_rol_id = $role['obj_id'];
				break;
			}
		}
		
		if((int)$a_rol_id)
		{		
			$user = $rbacadmin->deassignUser($a_rol_id, $a_usr_id);
			return true;
		}		
	
		return false;
	}
	
	public function getCurrentModerators()
	{
		global $rbacreview;
		
		$roles = $rbacreview->getRoleListByObject($this->getRefId());
		foreach($roles as $role)
		{
			if(strpos($role['title'], 'il_frm_moderator') !== false)
			{
				$assigned_users = $rbacreview->assignedUsers($role['rol_id']);
				break;
			}
		}
		return is_array($assigned_users) ? $assigned_users : array();
	}


	public function getUsers()
	{
		global $rbacreview;
		
		$roles = $rbacreview->getRoleListByObject($this->getRefId());
		foreach($roles as $role)
		{
			if(strpos($role['title'], 'il_frm_moderator') !== false)
			{
				$assigned_users = $rbacreview->assignedUsers($role['rol_id']);
				//vd($assigned_users);
				break;
			}
		}
		return is_array($assigned_users) ? $assigned_users : array();
	}
}

?>