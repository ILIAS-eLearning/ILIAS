<?php
include_once("classes/class.Object.php");
class GroupObject extends Object
{
	function GroupObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>