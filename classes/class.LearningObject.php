<?php
include_once("classes/class.Object.php");

/**
 * Class LearningObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/

class LearningObject extends Object
{
/**
 * constructor
 * @param object ilias
 * @access public
 */
	function LearningObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>