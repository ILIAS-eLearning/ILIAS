<?php
/**
* Class RootFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.RootFolderObjectOut.php,v 1.1 2002/12/20 14:31:03 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/
class RootFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function RootFolderObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "root";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.RootFolderObjectOut
?>