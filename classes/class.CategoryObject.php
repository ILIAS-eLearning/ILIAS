<?php
include_once("classes/class.Object.php");
class CategoryObject extends Object
{
	function CategoryObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>