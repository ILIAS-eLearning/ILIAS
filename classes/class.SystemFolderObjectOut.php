<?php
/**
* Class SystemFolderObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.SystemFolderObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/

class SystemFolderObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function SystemFolderObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "adm";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.SystemFolderObjectOut
?>