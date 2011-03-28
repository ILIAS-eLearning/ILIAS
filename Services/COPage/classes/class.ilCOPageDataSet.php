<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * COPage Data set class
 * 
 * This class implements the following entities:
 * - pgtp: page layout template
 * 
 * Please note that the usual page xml export DOES NOT use the dataset.
 * The page export uses pre-existing methods to create the xml.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCOPage
 */
class ilCOPageDataSet extends ilDataSet
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
			case "pgtp":
				return array("4.2.0");
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
		return "http://www.ilias.de/xml/Services/COPage/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		// pgtp: page layout template
		if ($a_entity == "pgtp")
		{
			switch ($a_version)
			{
				case "4.2.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"SpecialPage" => "integer",
						"StyleId" => "integer");
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
				
		// mep_data
		if ($a_entity == "pgtp")
		{
			switch ($a_version)
			{
				case "4.2.0":
					$this->getDirectDataFromQuery("SELECT layout_id id, title, description, ".
						" style_id, special_page ".
						" FROM page_layout ".
						"WHERE ".
						$ilDB->in("layout_id", $a_ids, false, "integer"));
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
	
	////
	//// Needs abstraction (interface?) and version handling
	////
	
	
	/**
	 * Import record
	 *
	 * @param
	 * @return
	 */
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
//echo $a_entity;
//var_dump($a_rec);
mk(); die(); //@todo;
		switch ($a_entity)
		{
			case "mep":
				include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");

				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjMediaPool();
					$newObj->setType("mep");
					$newObj->create(true);
				}
				
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setDefaultWidth($a_rec["DefaultWidth"]);
				$newObj->setDefaultHeight($a_rec["DefaultHeight"]);
				$newObj->update();
				
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/MediaPool", "mep", $a_rec["Id"], $newObj->getId());
				break;
		}
	}
}
?>