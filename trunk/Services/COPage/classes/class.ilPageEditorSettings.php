<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Page editor settings
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ServicesCOPage
*/
class ilPageEditorSettings
{
	// settings groups. each group contains one or multiple
	// page parent types
	protected static $option_groups = array(
		"lm" => array("lm", "dbk"),
		"wiki" => array("wpg"),
		"scorm" => array("sahs"),
		"glo" => array("gdf"),
		"test" => array("qpl"),
		"rep" => array("root", "cat", "grp", "crs", "fold")
		);
		
	/**
	* Get all settings groups
	*/
	static function getGroups()
	{
		return self::$option_groups;
	}
	
	/**
	* Write Setting
	*/
	static function writeSetting($a_grp, $a_name, $a_value)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM page_editor_settings WHERE ".
			"settings_grp = ".$ilDB->quote($a_grp, "text").
			" AND name = ".$ilDB->quote($a_name, "text")
			);
		
		$ilDB->manipulate("INSERT INTO page_editor_settings ".
			"(settings_grp, name, value) VALUES (".
			$ilDB->quote($a_grp, "text").",".
			$ilDB->quote($a_name, "text").",".
			$ilDB->quote($a_value, "text").
			")");
	}
	
	/**
	* Lookup setting
	*/
	static function lookupSetting($a_grp, $a_name, $a_default = false)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT value FROM page_editor_settings ".
			" WHERE settings_grp = ".$ilDB->quote($a_grp, "text").
			" AND name = ".$ilDB->quote($a_name, "text")
			);
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return $rec["value"];
		}
		
		return $a_default;
	}
	
	/**
	* Lookup setting by parent type
	*/
	static function lookupSettingByParentType($a_par_type, $a_name, $a_default = false)
	{
		foreach(self::$option_groups as $g => $types)
		{
			if (in_array($a_par_type, $types))
			{
				$grp = $g;
			}
		}
		
		if ($grp != "")
		{
			return ilPageEditorSettings::lookupSetting($grp, $a_name, $a_default);
		}
		
		return $a_default;
	}

}
?>