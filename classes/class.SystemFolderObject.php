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
class SystemFolderObject extends Object
{
/**
 * constructor
 * @param object ilias
 * @access public
 */
	function SystemFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>