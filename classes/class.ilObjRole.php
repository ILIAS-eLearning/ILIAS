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
* Class ilObjRole
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRole extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRole($a_id = 0,$a_call_by_reference = false)
	{
		$this->type = "role";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	/**
	* delete role object
	* @access	public
	*/
	function delete()
	{
		global $tree, $rbacadmin, $rbacreview;
		
		// TODO: unassign users from deleted role

		// check if role is a linked local role or not
		if ($rbacreview->isAssignable($this->getId(),$_GET["ref_id"]))
		{
			// do not delete role if this role is the last role a user is assigned to
			
			// first fetch all users assigned to role
			$user_ids = $rbacreview->assignedUsers($this->getId());
			
			$last_role_user_ids = array();

			foreach ($user_ids as $user_id)
			{
				// get all roles each user has
				$role_ids = $rbacreview->assignedRoles($user_id);
				
				// is last role?
				if (count($role_ids) == 1)
				{
					$last_role_user_ids[] = $user_id;
				}			
			}
			
			// users with last role found?
			if (count($last_role_user_ids) > 0)
			{
				foreach($last_role_user_ids as $user_id)
				{
					// GET OBJECT TITLE
					$tmp_obj = $this->ilias->obj_factory->getInstanceByObjId($user_id);
					$user_names[] = $tmp_obj->getFullname();
					unset($tmp_obj);
				}

				$users = implode(', ',$user_names);
				$this->ilias->raiseError($this->lng->txt("msg_user_last_role1")." ".
									 $users."<br/>".$this->lng->txt("msg_user_last_role2"),$this->ilias->error_obj->WARNING);				
			}
			else
			{
				// IT'S A BASE ROLE
				$rbacadmin->deleteRole($this->getId(),$_GET["ref_id"]);

				// delete object_data entry
				parent::delete();
			}
		}
		else
		{
			// linked local role: INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
			$rbacadmin->deleteLocalRole($this->getId(),$_GET["ref_id"]);
		}

		return true;
	}

	/**
	* update a role object
	* @access	public
	* @param	array	object data of role
	* @return	boolean
	*/
	function update()
	{
		global $rbacsystem, $rbacadmin, $rbacreview;

		// check if role title is unique
		if ($rbacreview->roleExists($this->getTitle()))
		{
			$this->ilias->raiseError("A role with the name '".$this->getTitle().
				 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
		}

		parent::update();
	}

} // END class.RoleObject
?>
