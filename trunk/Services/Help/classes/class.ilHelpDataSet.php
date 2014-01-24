<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Help system data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesHelp
 */
class ilHelpDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.3.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Services/Help/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "help_map")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"Chap" => "integer",
						"Component" => "text",
						"ScreenId" => "text",
						"ScreenSubId" => "text",
						"Perm" => "text"
					);
			}
		}

		if ($a_entity == "help_tooltip")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"Id" => "integer",
						"TtText" => "text",
						"TtId" => "text",
						"Comp" => "text",
						"Lang" => "text"
					);
			}
		}

	}

	/**
	 * Read data
	 *
	 * @param
	 * @return
	 */
	function readData($a_entity, $a_version, $a_ids, $a_field = "")
	{
		global $ilDB;

		if (!is_array($a_ids))
		{
			$a_ids = array($a_ids);
		}
				
		if ($a_entity == "help_map")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT chap, component, screen_id, screen_sub_id, perm ".
						" FROM help_map ".
						"WHERE ".
						$ilDB->in("chap", $a_ids, false, "integer"));
					break;
			}
		}
		
		if ($a_entity == "help_tooltip")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id, tt_text, tt_id, comp, lang FROM help_tooltip");
					break;
			}
		}

	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		return false;
	}
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		switch ($a_entity)
		{
			case "help_map":
				
				include_once("./Services/Help/classes/class.ilHelpMapping.php");
				
				// without module ID we do nothing
				$module_id = $a_mapping->getMapping('Services/Help','help_module', 0);
				if ($module_id)
				{
					$new_chap = $a_mapping->getMapping('Services/Help', 'help_chap',
						$a_rec["Chap"]);

					if ($new_chap > 0)
					{
						ilHelpMapping::saveMappingEntry($new_chap,
							$a_rec["Component"],
							$a_rec["ScreenId"],
							$a_rec["ScreenSubId"],
							$a_rec["Perm"],
							$module_id
							);
					}
				}
				break;
				
			case "help_tooltip":
				
				include_once("./Services/Help/classes/class.ilHelp.php");
				
				// without module ID we do nothing
				$module_id = $a_mapping->getMapping('Services/Help','help_module',0);
				if ($module_id)
				{
					ilHelp::addTooltip($a_rec["TtId"], $a_rec["TtText"], $module_id);
				}
				break;
		}
	}
}
?>