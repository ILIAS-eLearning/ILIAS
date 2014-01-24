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
	 * Get page object instance
	 *
	 * @param string $a_parent_type parent type
	 * @param int $a_id page id
	 * @param int $a_old_nr history number of page
	 * @param string $a_lang language
	 * @return object
	 */
	static function getInstance($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_lang = "-")
	{
		include_once("./Services/COPage/classes/class.ilCOPageObjDef.php");
		$def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
		$class = $def["class_name"];
		$path = "./".$def["component"]."/".$def["directory"]."/class.".$class.".php";
		include_once($path);
		$obj = new $class($a_id , $a_old_nr, $a_lang);
		
		return $obj;
	}
	
	/**
	 * Get page config instance
	 *
	 * @param string $a_parent_type parent type
	 * @return object
	 */
	static function getConfigInstance($a_parent_type)
	{
		include_once("./Services/COPage/classes/class.ilCOPageObjDef.php");
		$def = ilCOPageObjDef::getDefinitionByParentType($a_parent_type);
		$class = $def["class_name"]."Config";
		$path = "./".$def["component"]."/".$def["directory"]."/class.".$class.".php";
		include_once($path);
		$cfg = new $class();
		
		return $cfg;
	}
	
}

?>
