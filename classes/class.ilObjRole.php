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
		
		if ($rbacadmin->isAssignable($this->getId(),$_GET["ref_id"]))
		{
			// IT'S THE BASE ROLE
			$rbacadmin->deleteRole($this->getId(),$_GET["ref_id"]);
			
			// delete object_data entry
			parent::delete();
		}
		else
		{
			// INHERITANCE WAS STOPPED, SO DELETE ONLY THIS LOCAL ROLE
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
		global $rbacsystem, $rbacadmin;

		// check if role title is unique
		if ($rbacadmin->roleExists($this->getTitle()))
		{
			$this->ilias->raiseError("A role with the name '".$this->getTitle().
				 "' already exists! <br />Please choose another name.",$this->ilias->error_obj->MESSAGE);
		}

		parent::update();
	}

} // END class.RoleObject
?>
