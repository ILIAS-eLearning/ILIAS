<?php
/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjForumGUI.php,v 1.1 2003/03/24 15:41:43 akill Exp $
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
