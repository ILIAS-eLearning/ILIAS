<?php
/**
* Class ilObjLearningObject
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Sascha Hofmann <shofmann@databay.de> 
* $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObject.php";

class ilObjLearningObject extends ilObject
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
	function ilObjLearningObject($a_id = 0,$a_call_by_reference = true)
	{
		//require_once "classes/class.domxml.php";
		//$this->domxml = new domxml();
		$this->type = "lo";
		$this->ilObject($a_id,$a_call_by_reference);
	}
}
?>
