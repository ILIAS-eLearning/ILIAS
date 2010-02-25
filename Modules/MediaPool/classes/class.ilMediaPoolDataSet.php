<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Media Pool Data set class
 * 
 * This class implements the following entities:
 * - mep_data: data from table mep_data
 * - mep_tree: data from a join on mep_tree and mep_item
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesMediaPool
 */
class ilMediaPoolDataSet extends ilDataSet
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
			case "mep":
				return array("4.1.0");
			case "mep_data":
				return array("4.1.0");
			case "mep_tree":
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
		if ($a_entity != "mep")
		{
			return "http://www.ilias.de/xml/Modules/MediaPool/".$a_entity;
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
		// mep
		if ($a_entity == "mep")
		{
			return false;
		}
		
		// mep_data
		if ($a_entity == "mep_data")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"id" => "integer",
						"default_width" => "integer",
						"default_height" => "integer");
			}
		}

		// mep_tree
		if ($a_entity == "mep_tree")
		{
			switch ($a_version)
			{
				case "4.1.0":
						return array(
							"mep_id" => "integer",
							"child" => "integer",
							"parent" => "integer",
							"lft" => "integer",
							"rgt" => "integer",
							"depth" => "integer",
							"type" => "text",
							"title" => "text",
							"foreign_id" => "integer"
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
		
		// mep (no data, only dependent records)
		if ($a_entity == "mep")
		{
			$this->data = false;
		}
		
		// mep_data
		if ($a_entity == "mep_data")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT * FROM mep_data WHERE ".
						$ilDB->in("id", $a_ids, false, "integer"));
					break;
			}
		}	

		// mep_tree
		if ($a_entity == "mep_tree")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT mep_id, child ".
						" ,parent,lft,rgt,depth,type,title,foreign_id ".
						" FROM mep_tree JOIN mep_item ON (child = obj_id) ".
						" WHERE ".
						$ilDB->in("mep_id", $a_ids, false, "integer").
						" ORDER BY depth");
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
			case "mep":
				return array (
					"mep_data" => array("ids" => $a_ids)
				);
			
			case "mep_data":
				return array (
					"mep_tree" => array("ids" => $a_rec["id"])
				);
		}
		return false;
	}
	
	////
	//// Needs abstraction (interface?) and version handling
	////
	
	/**
	 * Init import
	 */
	function initImport($a_entities, $a_mappings)
	{
		// check entity types and versions
		// get mappings for other entities (here: mobs)
	}
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_record)
	{
		switch ($a_entity)
		{
			case "mep_data":
				break;

			case "mep_tree":
				break;
		}
	}
}
?>