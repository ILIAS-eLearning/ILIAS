<?php
/**
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.CourseObjectOut.php,v 1.3 2003/03/13 17:48:30 akill Exp $
* 
* @extends ilObjectGUI
* @package ilias-core
*/

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
