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
	*/
	function UserFolderObject($a_id,$a_call_by_reference = "")
	{
		$this->Object($a_id,$a_call_by_reference);
	}

	function getSubObjects()
	{
		return false;
	}
} // class
?>
