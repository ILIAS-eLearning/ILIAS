<?php
/**
* Class GroupObject
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/
class GroupObject extends Object
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function GroupObject($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = "grp";
		$this->Object($a_id,$a_call_by_reference);
	}
} //END class.GroupObject
?>
