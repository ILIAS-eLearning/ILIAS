<?php
/**
* Class UserObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* @extends Object
* @package ilias-core
*/
require_once "classes/class.Object.php";

class UserObject extends Object
{
	/**
	* array of gender abbreviations
	* @var array
	* @access public
	*/
	var $gender;

	/**
	* Contructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function UserObject($a_id = 0,$a_call_by_reference = false)
	{
		global $lng;

		$this->type = "usr";
		$this->Object($a_id,$a_call_by_reference);

		// for gender selection. don't change this
		$this->gender = array(
							  'm'    => "salutation_m",
							  'f'    => "salutation_f"
							  );
	}

	/**
	* delete user
	* @access	public
	*/
	function deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1)
	{
		global $rbacadmin;
		
		// delete user data
		$user = new User();
		$user->delete($a_obj_id);

		// delete rbac data of user
		$rbacadmin->removeUser($a_obj_id);

		// delete object_data entry
		return parent::deleteObject($a_obj_id, $a_parent_id, $a_tree_id = 1);
	}


	/**
	* add active role in session
	* @access	public
	**/
	function activeRoleSaveObject()
	{
		// TODO: get rif of $_POST var
	   if (!count($_POST["active"]))
	   {
		  $this->ilias->raiseError("You must leave one active role",$this->ilias->error_obj->MESSAGE);
	   }

	   $_SESSION["RoleId"] = $_POST["active"];

	   return true;
	}
} //end class.UserObject
?>
