<?php
include_once("classes/class.Object.php");

/**
 * Class TypeDefinitionObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
class TypeDefinitionObject extends Object
{
/**
 * constructor
 * @param object ilias
 * @access public
 */
	function TypeDefinitionObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}

}
?>