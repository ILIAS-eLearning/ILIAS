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
	*/
	function LearningObjectObject($a_id = 0,$a_call_by_reference = true)
	{
		//require_once "classes/class.domxml.php";
		//$this->domxml = new domxml();
		$this->Object($a_id,$a_call_by_reference);
		$this->setType("lo");
	}
} // END class.LearningObject
?>
