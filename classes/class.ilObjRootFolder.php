<?php
/**
* Class ilObjRootFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.ilObjRootFolder.php,v 1.1 2003/03/24 15:41:43 akill Exp $
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRootFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRootFolder($a_id,$a_call_by_reference = true)
	{
		$this->type = "root";
		$this->ilObject($a_id,$a_call_by_reference);
	}

	function deleteObject($a_obj_id, $a_parent, $a_tree_id = 1)
	{
		global $rbacadmin;

		// GET ALL ROLES OF ROLE FOLDER
		$all_roles = $rbacadmin->getRolesAssignedToFolder($a_obj_id);
		
		// FIRST DELETE THIS ROLES
		foreach($all_roles as $role_id)
		{
			include_once("classes/class.ilObjRole.php");

			$role_obj = new ilObjRole();
			$role_obj->deleteObject($role_id,$a_obj_id);
		}
		// NOW DELETE ROLE FOLDER
		parent::deleteObject($a_obj_id,$a_parent,$a_tree_id);
	}
} // END class.RootFolder
?>
