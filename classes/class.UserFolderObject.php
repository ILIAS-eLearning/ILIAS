<?php
/**
 * Class UserFolder
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
include_once("classes/class.Object.php");
class UserFolderObject extends Object
{
// PUBLIC METHODEN
/**
 * contructor
 * @param object ilias
 * @access public
 **/
	function UserFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>