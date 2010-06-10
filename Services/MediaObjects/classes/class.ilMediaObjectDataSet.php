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
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_target_release)
	{
		return "http://www.ilias.de/xml/Services/MediaObject/".$a_entity;
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
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"Dir" => "directory"
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
						"Id" => "integer",
						"MobId" => "integer",
						"Width" => "integer",
						"Height" => "integer",
						"Halign" => "text",
						"Caption" => "text",
						"Nr" => "integer",
						"Purpose" => "text",
						"Location" => "text",
						"LocationType" => "text",
						"Format" => "text",
						"Param" => "text",
						"TextRepresentation" => "text"
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
							"MiId" => "integer",
							"Nr" => "integer",
							"Shape" => "text",
							"Coords" => "text",
							"LinkType" => "text",
							"Title" => "text",
							"Href" => "text",
							"Target" => "text",
							"Type" => "text",
							"TargetFrame" => "text"
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
							"MiId" => "integer",
							"Name" => "text",
							"Value" => "text"
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

		// mob
		if ($a_entity == "mob")
		{
			$this->data = array();
			
			foreach ($a_ids as $mob_id)
			{
				if (ilObject::_lookupType($mob_id) == "mob")
				{
					$this->data[] = array ("Id" => $mob_id,
						"Title" => ilObject::_lookupTitle($mob_id),
						"Description" => ilObject::_lookupDescription($mob_id));
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
						$ilDB->in("mob_id", $a_ids, false, "integer"));
					break;
			}
		}	

		
		// media item map area
		if ($a_entity == "mob_mi_map_area")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT item_id, nr".
						" ,shape, coords, link_type, title, href, target, type, target_frame ".
						" FROM map_area ".
						" WHERE ".
						$ilDB->in("item_id", $a_ids, false, "integer").
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
					$this->getDirectDataFromQuery("SELECT med_item_id, name Name, value".
						" FROM mob_parameter ".
						" WHERE ".
						$ilDB->in("med_item_id", $a_ids, false, "integer"));
					break;
			}
		}			
		
	}
	
	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		switch ($a_entity)
		{
			case "mob":
				return array (
					"mob_media_item" => array("ids" => $a_rec["Id"])
				);
				
			case "mob_media_item":
				return array (
					"mob_mi_map_area" => array("ids" => $a_rec["Id"]),
					"mob_mi_parameter" => array("ids" => $a_rec["Id"])
				);
		}
		return false;
	}

	/**
	 * Get xml record
	 *
	 * @param
	 * @return
	 */
	function getXmlRecord($a_entity, $a_version, $a_set)
	{
		if ($a_entity == "mob")
		{
			include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
			$dir = ilObjMediaObject::_getDirectory($a_set["Id"]);
			$a_set["Dir"] = $dir;
		}

		return $a_set;
	}
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec)
	{
		switch ($a_entity)
		{
			case "mob":
				break;
		}
	}
	
}
?>