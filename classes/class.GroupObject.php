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
	*
	* qparam	int		$a_id	object id
	* @access	public
	*/
	function GroupObject($a_id = 0,$a_call_by_reference = "")
	{
		$this->Object($a_id,$a_call_by_reference);
	}
} //END class.GroupObject
?>
