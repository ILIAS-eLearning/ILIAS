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

require_once "class.ilObject.php";

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
	* @return	boolean
	**/
	function delete()
	{
		global $rbacsystem, $rbacadmin;

		// delete rbac permissions
		$rbacadmin->deleteTemplate($this->getId());

		// delete object data entry
		parent::delete();

		//TODO: delete references

		return true;
	}

} // END class.RoleTemplateObject
?>
