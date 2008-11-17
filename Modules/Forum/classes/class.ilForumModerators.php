<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Class ilForumModerators
*
* @author Nadia Krzywon <nkrzywon@databay.de>
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
	
	public function addModeratorRole($a_usr_id)
	{
		global $rbacreview, $rbacadmin;
		
		$role_folder_id = $rbacreview->getRoleFolderIdOfObject($this->ref_id); 
		$role_list = $rbacreview->getRoleListByObject($role_folder_id);
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
		
		$role_folder_id = $rbacreview->getRoleFolderIdOfObject($this->ref_id); 
		$role_list = $rbacreview->getRoleListByObject($role_folder_id);
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
		
		$role_folder = $rbacreview->getRoleFolderOfObject($this->ref_id);	
		$roles = $rbacreview->getRoleListByObject($role_folder['child']);
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
		
		$role_folder = $rbacreview->getRoleFolderOfObject($this->ref_id);	
		$roles = $rbacreview->getRoleListByObject($role_folder['child']);
		foreach($roles as $role)
		{
			if(strpos($role['title'], 'il_frm_moderator') !== false)
			{
				$assigned_users = $rbacreview->assignedUsers($role['rol_id']);
				vd($assigned_users);
				break;
			}
		}
		return is_array($assigned_users) ? $assigned_users : array();
	}
}

?>