<?php
/**
 * Class RoleFolderObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
include_once("classes/class.Object.php");
class RoleFolderObject extends Object
{
/**
 * contructor
 * @param object ilias
 * @access public
 **/
	function RoleFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>