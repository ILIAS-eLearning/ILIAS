<?php
/**
* Class ilObjCourse
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class ilObjCourse extends ilObject
{
	/**
	* Constructor
	*
	* @param	int		$a_id		object id
	* @access	public
	*/
	function ilObjCourse($a_id,$a_call_by_reference = true)
	{
		$this->ilObject($a_id,$a_call_by_reference);
	}
} //END class.CourseObject
?>
