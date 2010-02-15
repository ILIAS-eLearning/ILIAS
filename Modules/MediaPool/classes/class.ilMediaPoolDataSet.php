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
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes()
	{
		// mep_data
		if ($this->entity == "mep_data")
		{
			switch ($this->version)
			{
				case "4.1.0":
					return array(
						"id" => "integer",
						"default_width" => "integer",
						"default_height" => "integer");
			}
		}

		// mep_tree
		if ($this->entity == "mep_tree")
		{
			switch ($this->version)
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
	function readData($a_where)
	{
		global $ilDB;
		
		// mep_data
		if ($this->entity == "mep_data")
		{
			switch ($this->version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT * FROM mep_data WHERE ".
						" id = ".$ilDB->quote($a_where["id"], "integer"));
					break;
			}
		}	

		// mep_tree
		if ($this->entity == "mep_tree")
		{
			switch ($this->version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT mep_id, child ".
						" ,parent,lft,rgt,depth,type,title,foreign_id ".
						" FROM mep_tree JOIN mep_item ON (child = obj_id) ".
						" WHERE ".
						" mep_id = ".$ilDB->quote($a_where["mep_id"], "integer").
						" ORDER BY depth");
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
			case "mep":
				return array (
					"mep_data" => array(
						"where" => array("id" => $a_where["id"])),
					"mep_tree" => array(
						"where" => array("mep_id" => $a_where["id"]))
				);
		}
		return false;
	}
	
}
?>