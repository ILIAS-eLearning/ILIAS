<?php
/**
* Class ilObjRoleTemplate
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObject
* @package ilias-core
*/
class ilObjRoleTemplate extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRoleTemplate($a_id = 0,$a_call_by_reference = false)
	{
		$this->type = "rolt";
		$this->ilObject($a_id,$a_call_by_reference);
	}


	/**
	* delete a role template object 
	* @access	public
	* @param	integer	object_id
	* @param	integer parent_id // WE DON'T NEED THIS
	* @param	integer	tree_id // WE DON'T NEED THIS
	* @return	boolean
	**/
	function deleteObject($a_obj_id, $a_parent, $a_tree_id = 1)
	{
		global $rbacsystem, $rbacadmin;

		// delete rbac permissions
		$rbacadmin->deleteTemplate($a_obj_id);

		// delete object data entry
		deleteObject($a_obj_id);

		//TODO: delete references

		return true;
	}


	/**
	* copy permissions from role or template
	* @access	public
	**/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem;

		if ($rbacsystem->checkAccess('edit permission',$_GET["parent"]))
		{
			$rbacadmin->deleteRolePermission($_GET["obj_id"],$_GET["parent"]);
			$parentRoles = $rbacadmin->getParentRoleIds($_GET["parent"],$_GET["parent_parent"],true);
			$rbacadmin->copyRolePermission($_POST["adopt"],$parentRoles["$_POST[adopt]"]["parent"],$_GET["parent"],$_GET["obj_id"]);
		}
		else
		{
			$this->ilias->raiseError("No Permission to edit permissions",$this->ilias->error_obj->WARNING);
		}

		return true;
	}
} // END class.RoleTemplateObject
?>
