<?php
include_once("classes/class.Object.php");

/**
 * Class ObjectFolderObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
class ObjectFolderObject extends Object
{
/**
 * constructor
 * @param object ilias
 * @access public
 **/
	function ObjectFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>