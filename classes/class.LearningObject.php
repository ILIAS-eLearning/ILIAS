<?php
/**
 * Class LearningObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
include_once("classes/class.Object.php");
class LearningObject extends Object
{
/**
 * contructor
 * @param object ilias
 * @access public
 **/
	function LearningObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>