<?php
/**
* Class SystemFolderObject
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class SystemFolderObject extends Object
{
	/**
	* Constructor
	* @access	public
	*/
	function SystemFolderObject($a_id,$a_call_by_reference = true)
	{
		$this->Object($a_id,$a_call_by_reference);
	}
} // END class.SystemFolderObject
?>
