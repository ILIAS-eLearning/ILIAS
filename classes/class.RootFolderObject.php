<?php
/**
* Class RootFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.RootFolderObject.php,v 1.6 2003/03/17 17:44:43 shofmann Exp $
* 
* @extends Object
* @package ilias-core
*/
class RootFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function RootFolderObject($a_id,$a_call_by_reference = true)
	{
		$this->type = "root";
		$this->Object($a_id,$a_call_by_reference);
	}

	function deleteObject($a_obj_id, $a_parent, $a_tree_id = 1)
	{
		global $rbacadmin;

		// GET ALL ROLES OF ROLE FOLDER
		$all_roles = $rbacadmin->getRolesAssignedToFolder($a_obj_id);
		
		// FIRST DELETE THIS ROLES
		foreach($all_roles as $role_id)
		{
			include_once("classes/class.RoleObject.php");

			$role_obj = new RoleObject();
			$role_obj->deleteObject($role_id,$a_obj_id);
		}
		// NOW DELETE ROLE FOLDER
		parent::deleteObject($a_obj_id,$a_parent,$a_tree_id);
	}
} // END class.RootFolder
?>
