<?php
include_once("classes/class.Object.php");

/**
 * Class UserFolder
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
class UserFolderObject extends Object
{
// PUBLIC METHODEN
/**
 * constructor
 * @param object ilias
 * @access public
 */
	function UserFolderObject(&$a_ilias)
	{
		$this->Object($a_ilias);
	}
}
?>