<?php
/**
* Class CategoryObjectOut
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.CategoryObjectOut.php,v 1.2 2003/03/10 10:55:41 shofmann Exp $
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
