<?php
/**
 * Class ObjectFolderObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$
 * 
 */
include_once("classes/class.Object.php");
class SystemFolderObject extends Object
{
	function SystemFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>