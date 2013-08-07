<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page object factory 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilPageObjectFactory
{
	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	function getInstance($a_parent_type, $a_id = 0, $a_old_nr = 0)
	{
		include_once("./Services/COPage/classes/class.ilCOPageObjDef.php");
		$def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
		$class = $def["class_name"];
		$path = "./".$def["component"]."/".$def["directory"]."/class.".$class.".php";
		include_once($path);
		$obj = new $class($a_id , $a_old_nr);
		
		return $obj;
	}
	
}

?>
