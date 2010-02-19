<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Media Pool Data set class
 * 
 * This class implements the following entities:
 * - mob: object data
 * - mob_media_item: data from table media_item
 * - mob_mi_map_area: data from a table map_area
 * - mob_mi_parameter: data from a table mob_parameter
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesMediaPool
 */
class ilMediaObjectDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions($a_entity)
	{
		switch ($a_entity)
		{
			case "mob":
				return array("4.1.0");
			case "mob_media_item":
				return array("4.1.0");
			case "mob_mi_map_area":
				return array("4.1.0");
			case "mob_mi_parameter":
				return array("4.1.0");
		}
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		// mob
		if ($a_entity == "mob")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"id" => "integer",
						"title" => "text",
						"description" => "text"
						);
			}
		}
		
		// media item
		if ($a_entity == "mob_media_item")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"id" => "integer",
						"mob_id" => "integer",
						"width" => "integer",
						"height" => "integer",
						"halign" => "text",
						"caption" => "text",
						"nr" => "integer",
						"purpose" => "text",
						"location" => "text",
						"location_type" => "text",
						"format" => "text",
						"param" => "text",
						"text_representation" => "text"
					);
			}
		}

		// map areas
		if ($a_entity == "mob_mi_map_area")
		{
			switch ($a_version)
			{
				case "4.1.0":
						return array(
							"mi_id" => "integer",
							"nr" => "integer",
							"shape" => "text",
							"coords" => "text",
							"link_type" => "text",
							"title" => "text",
							"href" => "text",
							"target" => "text",
							"type" => "text",
							"target_frame" => "text"
						);
			}
		}				

		// media item parameter
		if ($a_entity == "mob_mi_parameter")
		{
			switch ($a_version)
			{
				case "4.1.0":
						return array(
							"mi_id" => "integer",
							"name" => "text",
							"value" => "text"
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
	function readData($a_entity, $a_version, $a_where)
	{
		global $ilDB;
		
		// mob
		if ($a_entity == "mob")
		{
			$this->data = array();
			if (!is_array($a_where["id"]))
			{
				$a_where["id"] = array($a_where["id"]);
			}
			
			foreach ($a_where["id"] as $mob_id)
			{
				if (ilObject::_lookupType($mob_id) == "mob")
				{
					$this->data[] = array ("id" => $mob_id,
						"title" => ilObject::_lookupTitle($mob_id),
						"description" => ilObject::_lookupDescription($mob_id));
				}
			}
		}

		// media item
		if ($a_entity == "mob_media_item")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT id, mob_id, width, height, halign,".
						"caption, nr, purpose, location, location_type, format, param, text_representation".
						" FROM media_item WHERE ".
						" mob_id = ".$ilDB->quote($a_where["mob_id"], "integer"));
					break;
			}
		}	

		
		// media item map area
		if ($a_entity == "mob_mi_map_area")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT item_id mi_id, nr ".
						" ,shape, coords, link_type, title, href, target, type, target_frame ".
						" FROM map_area ".
						" WHERE ".
						" item_id = ".$ilDB->quote($a_where["mi_id"], "integer").
						" ORDER BY nr");
					break;
			}
		}			

		// media item parameter
		if ($a_entity == "mob_mi_parameter")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT med_item_id mi_id, name, value ".
						" FROM mob_parameter ".
						" WHERE ".
						" med_item_id = ".$ilDB->quote($a_where["mi_id"], "integer"));
					break;
			}
		}			
		
	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_where)
	{
		switch ($a_entity)
		{
			case "mob":
				return array (
					"mob_media_item" => array(
						"where" => array("mob_id" => $a_rec["id"]))
				);
				
			case "mob_media_item":
				return array (
					"mob_mi_map_area" => array(
						"where" => array("mi_id" => $a_rec["id"])),
					"mob_mi_parameter" => array(
						"where" => array("mi_id" => $a_rec["id"]))
				);
		}
		return false;
	}
	
}
?>