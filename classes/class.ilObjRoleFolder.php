<?php
/**
* Class ilObjRoleFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjRoleFolder extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjRoleFolder($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "rolf";
		$this->ilObject($a_id,$a_call_by_reference);
	}


	function deleteObject($a_obj_id,$a_parent)
	{
		global $rbacadmin;


		$roles = $rbacadmin->getRolesAssignedToFolder($a_obj_id);

		// FIRST DELETE ALL LOCAL/BASE ROLES OF FOLDER
		require_once("./classes/class.ilObjRole.php");
		$obj = new ilObjRole();
		
		foreach($roles as $role)
		{
			$obj->deleteObject($role,$a_obj_id);
		}

		// DELETE ROLE FOLDER
		parent::deleteObject($a_obj_id,$a_parent);
		return true;
	}

	function clone($a_parent_ref)
	{
		// DO NOTHING ROLE FOLDERS AREN'T COPIED
		//	$new_id = parent::clone($a_parent_ref);
		return true;
	}

	function getSubObjects()	
	{
		return false;
	} //function

} // class
?>
