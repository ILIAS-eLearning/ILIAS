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
	function LearningObjectObject($a_id="")
	{
		//require_once "classes/class.domxml.php";
		//$this->domxml = new domxml();
		$this->Object($a_id);
	}
} // END class.LearningObject
?>
