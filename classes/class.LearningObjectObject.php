<?php
/**
* Class LearningObject
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de> 
* $Id$
* 
* @extends Object
* @package ilias-core
*/

class LearningObjectObject extends Object
{
	/**
	* domxml object
	* 
	* @var		object	domxml object
	* @access	public 
	*/
	//var $domxml;	
	
	/**
	* Constructor
	* @access public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function LearningObjectObject($a_id = 0,$a_call_by_reference = true)
	{
		//require_once "classes/class.domxml.php";
		//$this->domxml = new domxml();
		$this->type = "lo";
		$this->Object($a_id,$a_call_by_reference);
	}
} // END class.LearningObject
?>
