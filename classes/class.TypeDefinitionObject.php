<?php
/**
 * Class TypeDefinitionObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
include_once("classes/class.Object.php");
class TypeDefinitionObject extends Object
{
/**
 * contructor
 * @param object ilias
 * @access public
 **/
	function TypeDefinitionObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}

}
?>