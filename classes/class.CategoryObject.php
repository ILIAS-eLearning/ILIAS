<?php
/**
 * Class CategoryObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
include_once("classes/class.Object.php");
class CategoryObject extends Object
{
	function CategoryObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>