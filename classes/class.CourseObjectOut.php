<?php
/**
* Class CourseObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.CourseObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
* 
* @extends Object
* @package ilias-core
*/

class CourseObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function CourseObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "crs";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.CourseObjectOut
?>
