<?php
/**
* Class ilObjRootFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.ilObjRootFolder.php,v 1.2 2003/03/28 10:30:36 shofmann Exp $
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

	/*
	* deletion (no deletion of root folder)
	*/
	function delete()
	{
		global $rbacadmin;
		
		$this->ilias->raiseError("ilObjRootFolder::delete(): Can't delete root folder", $this->ilias->error_obj->MESSAGE);

		// GET ALL ROLES OF ROLE FOLDER
		/*
		$all_roles = $rbacadmin->getRolesAssignedToFolder($this->getId());
		
		// FIRST DELETE THIS ROLES
		foreach($all_roles as $role_id)
		{
			$role_obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);
			$role_obj->delete();
		}
		// NOW DELETE ROLE FOLDER
		parent::delete();*/
	}
} // END class.RootFolder
?>
