<?php
/**
* Class ilObjRootFolderGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$Id: class.RootFolderObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/
class ilObjRootFolderGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjRootFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "root";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
}
?>
