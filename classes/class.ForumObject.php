<?php
/**
 * Class ForumObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
include_once("classes/class.Object.php");
class ForumObject extends Object
{
/**
 * contructor
 * @param object ilias
 * @access public
 **/
	function ForumObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>