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
				self::$pc_def[$rec["pc_type"]] = $rec;
				self::$pc_def_by_name[$rec["name"]] = $rec;
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
		require_once($pc_path);
	}
	
}

?>
