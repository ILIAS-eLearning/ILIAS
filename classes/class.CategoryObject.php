<?php

include_once("classes/class.Object.php");

/**
 * Class CategoryObject
 * @extends class.Object.php
 * @author Stefan Meyer <smeyer@databay.de> 
 * @version $Id$ 
 * @package ilias-core
 * 
*/
class CategoryObject extends Object
{
	
	/**
	* @param object db
	*/
	function CategoryObject(&$a_dbhandle)
	{
		$this->Object($a_dbhandle);
	}
}
?>