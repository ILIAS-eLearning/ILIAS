<?php
/**
 * Class ForumObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
include_once("classes/class.Object.php");
class ForumObject extends Object
{
	function ForumObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>