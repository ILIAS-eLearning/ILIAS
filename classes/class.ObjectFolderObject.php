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
	function ObjectFolderObject($a_id)
	{
		$this->Object($a_id);
	}


	function getSubObjects()
	{
		return false;
	} //function

} // END class.ObjectFolderObject
?>
