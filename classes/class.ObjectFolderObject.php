<?php
/**
* Class ObjectFolderObject
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends Object
* @package ilias-core
*/
class ObjectFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	**/
	function ObjectFolderObject($a_id,$a_call_by_reference = "")
	{
		$this->Object($a_id,$a_call_by_reference);
	}


	function getSubObjects()
	{
		return false;
	} //function

} // END class.ObjectFolderObject
?>
