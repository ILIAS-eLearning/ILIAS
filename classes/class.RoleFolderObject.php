<?php
include_once("classes/class.Object.php");

/**
 * Class RoleFolderObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
class RoleFolderObject extends Object
{
/**
 * constructor
 * @param object ilias
 * @access public
*/
	function RoleFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>