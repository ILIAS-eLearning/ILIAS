<?php
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
		global $tree, $rbacadmin;
		
		if($rbacadmin->isAssignable($this->getId()))
		{
			// IT'S THE BASE ROLE
			$rbacadmin->deleteRole($this->getId());
			
			//remove role entry in object_data
			parent::delete();
			
			//TODO: delete references	
		}
		else
		{
			// INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
			$rbacadmin->deleteLocalRole($this->getId());

			//TODO: delete references	
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
		global $rbacsystem, $rbacadmin;

		// check if role title is unique
		if ($rbacadmin->roleExists($this->getTitle()))
		{
			$this->ilias->raiseError("A role with the name '".$this->getTitle().
				 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
		}

		parent::update();
	}

	/**
	* copy permissions from role
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem;
		
		// TODO: get rid of $_GET variables

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"], $_GET["parent"]);
			$parentRoles = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles[$_POST["adopt"]]["parent"],
										   $_GET["parent"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_obj->WARNING);
		}
		return true;
	}

	/**
	* assign user to role
	* @access	public
	*/
	function assignSaveObject()
	{
		global $tree, $rbacsystem, $rbacadmin, $rbacreview;
		
		// TODO: get rid of $_GET variables
		 
		if ($rbacadmin->isAssignable($_GET["obj_id"],$_GET["parent"]))
		{
			if ($rbacsystem->checkAccess('edit permission',$_GET["parent"],$_GET["parent_parent"]))
			{
				$assigned_users = $rbacreview->assignedUsers($_GET["obj_id"]);
				$_POST["user"] = $_POST["user"] ? $_POST["user"] : array();

				foreach (array_diff($assigned_users,$_POST["user"]) as $user)
				{
					$rbacadmin->deassignUser($_GET["obj_id"],$user);
				}

				foreach (array_diff($_POST["user"],$assigned_users) as $user)
				{
					$rbacadmin->assignUser($_GET["obj_id"],$user,false);
				}
			}
			else
			{
				$this->ilias->raiseError("No permission to edit permissions",$this->ilias->error_obj->WARNING);
			}

			return true;
		}
		else
		{
			$this->ilias->raiseError("It's worth a try. ;-)",$this->ilias->error_obj->WARNING);
		}
	}
} // END class.RoleObject
?>
