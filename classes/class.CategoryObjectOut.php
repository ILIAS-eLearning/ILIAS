<?php
/**
* Class CategoryObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.CategoryObjectOut.php,v 1.1 2002/12/03 16:50:15 smeyer Exp $
* 
* @extends Object
* @package ilias-core
*/

class CategoryObjectOut extends ObjectOut
{
	/**
	* Constructor
	* @access public
	*/
	function CategoryObjectOut($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "cat";
		$this->ObjectOut($a_data,$a_id,$a_call_by_reference);
	}
} // END class.LeraningObject
?>