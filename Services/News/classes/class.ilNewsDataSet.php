<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * News data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesNews
 */
class ilNewsDataSet extends ilDataSet
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
		return "http://www.ilias.de/xml/Services/News/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "news")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Content" => "text",
						"Priority" => "integer",
						"ContextObjId" => "integer",
						"ContextObjType" => "text",
						"ContextSubObjId" => "integer",
						"ContextSubObjType" => "text",
						"ContentType" => "text",
						"Visibility" => "text",
						"ContentLong" => "text",
						"ContentIsLangVar" => "integer",
						"MobId" => "integer",
						"Playtime" => "text"
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
				
		if ($a_entity == "news")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT id, title, content, priority,".
						" context_obj_id, context_obj_type, context_sub_obj_id, context_sub_obj_type, ".
						" content_type, visibility, content_long, content_is_lang_var, mob_id, playtime".
						" FROM il_news_item ".
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
			case "news":
				$mob_id = null;
				if ($a_rec["MobId"] > 0)
				{
					$mob_id = $a_mapping->getMapping("Services/MediaObjects", "mob", $a_rec["MobId"]);
				}
				$c = (int)$a_rec["ContextObjId"].":".$a_rec["ContextObjType"].":".(int)$a_rec["ContextSubObjId"].
					":".$a_rec["ContextSubObjType"];
				$context = $a_mapping->getMapping("Services/News", "news_context", $c);
				$context = explode(":", $context);
//var_dump($c);
//var_dump($a_mapping->mappings["Services/News"]["news_context"]);
				include_once("./Services/News/classes/class.ilNewsItem.php");
				$newObj = new ilNewsItem();
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setContent($a_rec["Content"]);
				$newObj->setPriority($a_rec["Priority"]);
				$newObj->setContextObjId($context[0]);
				$newObj->setContextObjType($context[1]);
				$newObj->setContextSubObjId($context[2]);
				$newObj->setContextSubObjType($context[3]);
				$newObj->setContentType($a_rec["ContentType"]);
				$newObj->setVisibility($a_rec["Visibility"]);
				$newObj->setContentLong($a_rec["ContentLong"]);
				$newObj->setContentIsLangVar($a_rec["ContentIsLangVar"]);
				$newObj->setMobId($mob_id);
				$newObj->setPlaytime($a_rec["Playtime"]);
				$newObj->create();
				$a_mapping->addMapping("Services/News", "news", $a_rec["Id"], $newObj->getId());
				break;
		}
	}
}
?>