<?php
/**
* Class RoleFolderObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/
class RoleFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function RoleFolderObject($a_id = 0,$a_call_by_reference = true)
	{
		$this->Object($a_id,$a_call_by_reference);
		$this->type = "rolf";
	}


	function deleteObject($a_obj_id,$a_parent)
	{
		global $rbacadmin;


		$roles = $rbacadmin->getRolesAssignedToFolder($a_obj_id);

		// FIRST DELETE ALL LOCAL/BASE ROLES OF FOLDER
		require_once("./classes/class.RoleObject.php");
		$obj = new RoleObject();
		
		foreach($roles as $role)
		{
			$obj->deleteObject($role,$a_obj_id);
		}

		// DELETE ROLE FOLDER
		parent::deleteObject($a_obj_id,$a_parent);
		return true;
	}

	function cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent)
	{
		// DO NOTHING ROLE FOLDERS AREN'T COPIED
		//	$new_id = parent::cloneObject($a_obj_id,$a_parent,$a_dest_id,$a_dest_parent);
		return true;
	}

	function getSubObjects()	
	{
		return false;
	} //function

} // class
?>
