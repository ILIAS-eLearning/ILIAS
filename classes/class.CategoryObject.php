<?php
/**
* Class CategoryObject
* 
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
*
* @extends Object
* @package ilias-core
*/
class CategoryObject extends Object
{
	/**
	* Constructor
	* @param	integer		$a_id		reference_id
	* @access	public
	*/
	function CategoryObject($a_id,$a_call_by_reference = true)
	{
		$this->Object($a_id,$a_call_by_reference);
	}
} // END class.CategoryObject
?>
