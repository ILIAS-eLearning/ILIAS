<?php
/**
* Class CourseObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.CourseObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
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