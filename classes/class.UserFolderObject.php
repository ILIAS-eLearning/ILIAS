<?php
/**
* Class UserFolder
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class UserFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function UserFolderObject($a_id,$a_call_by_reference = true)
	{
		$this->type = "usrf";
		$this->Object($a_id,$a_call_by_reference);
	}

	function getSubObjects()
	{
		return false;
	}
} // class
?>
