<?php
/**
* Class CourseObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class CourseObject extends Object
{
	/**
	* Constructor
	*
	* @param	int		$a_id		object id
	* @access	public
	*/
	function CourseObject($a_id,$a_call_by_reference = "")
	{
		$this->Object($a_id,$a_call_by_reference);
	}
} //END class.CourseObject
?>
