<?php
/**
* Class ilObjForumGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ForumObjectOut.php,v 1.3 2003/03/13 17:48:30 akill Exp $
* 
* @extends ilObject
* @package ilias-core
*/

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
