<?php
/**
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjCourseGUI.php,v 1.1 2003/03/24 15:41:43 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjCourseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "crs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}
}
?>
