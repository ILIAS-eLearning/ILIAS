<?php
/**
 * Class GroupObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * $Id$ 
 * 
*/
include_once("classes/class.Object.php");
class GroupObject extends Object
{
	function GroupObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>