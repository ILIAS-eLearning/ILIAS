<?php
/**
* Class ilObjSystemFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.SystemFolderObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

class ilObjSystemFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjSystemFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "adm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
} // END class.SystemFolderObjectOut
?>
