<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * COPage PC elements definition handler 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPagePCDef
{
	static $pc_def = null;
	static $pc_def_by_name = null;
	static $pc_gui_classes = array();
	static $pc_gui_classes_lc = array();
	static $pc_def_by_gui_class_cl = array();
	
	/**
	 * Init
	 *
	 * @param
	 * @return
	 */
	static function init()
	{
		global $ilDB;
		
		if (self::$pc_def == null)
		{
			$set = $ilDB->query("SELECT * FROM copg_pc_def ");
			while ($rec = $ilDB->fetchAssoc($set))
			{
				$rec["pc_class"] = "ilPC".$rec["name"];
				$rec["pc_gui_class"] = "ilPC".$rec["name"]."GUI";
				self::$pc_gui_classes[] = $rec["pc_gui_class"];
				self::$pc_gui_classes_lc[] = strtolower($rec["pc_gui_class"]); 
				self::$pc_def[$rec["pc_type"]] = $rec;
				self::$pc_def_by_name[$rec["name"]] = $rec;
				self::$pc_def_by_gui_class_cl[strtolower($rec["pc_gui_class"])] = $rec;
			}
		}
	}
	
	
	/**
	 * Get PC definitions
	 *
	 * @param
	 * @return
	 */
	function getPCDefinitions()
	{
		self::init();
		return self::$pc_def;
	}
	
	/**
	 * Get PC definition by type
	 *
	 * @param string type
	 * @return array definition
	 */
	function getPCDefinitionByType($a_pc_type)
	{
		self::init();
		return self::$pc_def[$a_pc_type];
	}
	
	/**
	 * Get PC definition by name
	 *
	 * @param string name
	 * @return array definition
	 */
	function getPCDefinitionByName($a_pc_name)
	{
		self::init();
		return self::$pc_def_by_name[$a_pc_name];
	}
	
	/**
	 * Get PC definition by name
	 *
	 * @param string name
	 * @return array definition
	 */
	function getPCDefinitionByGUIClassName($a_gui_class_name)
	{
		self::init();
		$a_gui_class_name = strtolower($a_gui_class_name);
		return self::$pc_def_by_gui_class_cl[$a_gui_class_name];
	}
	
	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function requirePCClassByName($a_name)
	{
		$pc_def = self::getPCDefinitionByName($a_name);
		$pc_class = "ilPC".$pc_def["name"];
		$pc_path = "./".$pc_def["component"]."/".$pc_def["directory"]."/class.".$pc_class.".php";
		include_once($pc_path);
	}
	
	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function requirePCGUIClassByName($a_name)
	{
		$pc_def = self::getPCDefinitionByName($a_name);
		$pc_class = "ilPC".$pc_def["name"]."GUI";
		$pc_path = "./".$pc_def["component"]."/".$pc_def["directory"]."/class.".$pc_class.".php";
		include_once($pc_path);
	}
	
	/**
	 * Is given class name a pc gui class?
	 *
	 * @param
	 * @return
	 */
	function isPCGUIClassName($a_class_name, $a_lower_case = false)
	{
		if ($a_lower_case)
		{
			return in_array($a_class_name, self::$pc_gui_classes_lc);
		}
		else
		{
			return in_array($a_class_name, self::$pc_gui_classes);
		}
	}
	
	
}

?>
