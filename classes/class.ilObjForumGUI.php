<?php
/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjForumGUI.php,v 1.2 2003/03/28 10:30:36 shofmann Exp $
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjForumGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjForumGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "frm";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
}
?>