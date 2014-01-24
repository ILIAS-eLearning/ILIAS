<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * External feed data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesExternalFeed
 */
class ilExternalFeedDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.1.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Modules/ExternalFeed/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "feed")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Url" => "text");
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
				
		if ($a_entity == "feed")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT obj_id id, title, description url ".
						" FROM object_data ".
						"WHERE ".
						$ilDB->in("obj_id", $a_ids, false, "integer"));
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
//echo $a_entity;
//var_dump($a_rec);

		switch ($a_entity)
		{
			case "feed":

				include_once("./Modules/ExternalFeed/classes/class.ilObjExternalFeed.php");

				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjExternalFeed();
					$newObj->setType("feed");
					$newObj->create(true);
				}

				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Url"]);
				$newObj->update();
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/ExternalFeed", "feed", $a_rec["Id"], $newObj->getId());

				// create the feed block
				include_once("./Services/Block/classes/class.ilExternalFeedBlock.php");
				$fb = new ilExternalFeedBlock();
				$fb->setTitle($a_rec["Title"]);
				$fb->setFeedUrl($a_rec["Url"]);
				$fb->setContextObjId($newObj->getId());
				$fb->setContextObjType("feed");
				$fb->create();

				break;
		}
	}
}
?>