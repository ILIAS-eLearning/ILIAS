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
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ObjectFolderObject($a_id,$a_call_by_reference = true)
	{
		$this->type = "objf";
		$this->Object($a_id,$a_call_by_reference);
	}


	function getSubObjects()
	{
		return false;
	} //function

} // END class.ObjectFolderObject
?>
