<?php
/**
* Class RootFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.RootFolderObject.php,v 1.3 2003/01/16 14:09:48 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/
class RootFolderObject extends Object
{
	/**
	* Constructor
	* @access public
	*/
	function RootFolderObject($a_id)
	{
		$this->Object($a_id);
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
