<?php
/**
* Class ilObjUser
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* @extends ilObject
* @package ilias-core
*/

require_once "classes/class.ilObject.php";

class ilObjUser extends ilObject
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
	function ilObjUser($a_id = 0,$a_call_by_reference = false)
	{
		global $lng;

		$this->type = "usr";
		$this->ilObject($a_id,$a_call_by_reference);

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
	function delete()
	{
		global $rbacadmin;
		
		// delete user data
		$user = new ilUser();
		$user->delete($this->getId());

		// delete rbac data of user
		$rbacadmin->removeUser($this->getId());

		// delete object_data entry
		return parent::delete();
	}

} //end class.UserObject
?>
