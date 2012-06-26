<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Media cast data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesMediaCast
 */
class ilMediaCastDataSet extends ilDataSet
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
		return "http://www.ilias.de/xml/Modules/MediaCast/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "mcst")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"PublicFiles" => "integer",
						"Downloadable" => "integer",
						"DefaultAccess" => "integer");
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
				
		if ($a_entity == "mcst")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT id, title, description, ".
						" public_files, downloadable, def_access default_access".
						" FROM il_media_cast_data JOIN object_data ON (il_media_cast_data.id = object_data.obj_id) ".
						"WHERE ".
						$ilDB->in("id", $a_ids, false, "integer"));
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
			case "mcst":
				include_once("./Modules/MediaCast/classes/class.ilObjMediaCast.php");
				
				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjMediaCast();
					$newObj->setType("mcst");
					$newObj->create(true);
				}
				
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setDefaultAccess($a_rec["DefaultAccess"]);
				$newObj->setDownloadable($a_rec["Downloadable"]);
				$newObj->setPublicFiles($a_rec["PublicFiles"]);
				$newObj->update(true);
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/MediaCast", "mcst", $a_rec["Id"], $newObj->getId());
				$a_mapping->addMapping("Services/News", "news_context",
					$a_rec["Id"].":mcst:0:",
					$newObj->getId().":mcst:0:");
//var_dump($a_mapping->mappings["Services/News"]["news_context"]);
				break;
		}
	}
}
?>