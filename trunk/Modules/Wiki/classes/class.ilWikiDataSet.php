<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Wiki Data set class
 * 
 * This class implements the following entities:
 * - wiki: data from il_wiki_data
 * - wpg: data from il_wiki_page
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesWiki
 */
class ilWikiDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.1.0", "4.3.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Modules/Wiki/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "wiki")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"StartPage" => "text",
						"Short" => "text",
						"Introduction" => "text",
						"Rating" => "integer");
					
				case "4.3.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"StartPage" => "text",
						"Short" => "text",
						"Introduction" => "text",
						"Rating" => "integer",
						"PublicNotes" => "integer",
						// "ImpPages" => "integer",
						"PageToc" => "integer",
						"RatingSide" => "integer",
						"RatingNew" => "integer",
						"RatingExt" => "integer");
					
				case "4.4.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"StartPage" => "text",
						"Short" => "text",
						"Introduction" => "text",
						"Rating" => "integer",
						"PublicNotes" => "integer",
						// "ImpPages" => "integer",
						"PageToc" => "integer",
						"RatingSide" => "integer",
						"RatingNew" => "integer",
						"RatingExt" => "integer",
						"RatingOverall" => "integer");
			}
		}

		if ($a_entity == "wpg")
		{
			switch ($a_version)
			{
				case "4.1.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"WikiId" => "integer");
					
				case "4.3.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"WikiId" => "integer",
						"Blocked" => "integer",
						"Rating" => "integer");
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
				
		if ($a_entity == "wiki")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT id, title, description,".
						" startpage start_page, short, rating, introduction". 
						" FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)".
						" WHERE ".$ilDB->in("id", $a_ids, false, "integer"));
					break;
				
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id, title, description,".
						" startpage start_page, short, rating, introduction,". // imp_pages,
						" public_notes, page_toc, rating_side, rating_new, rating_ext".
						" FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)".
						" WHERE ".$ilDB->in("id", $a_ids, false, "integer"));
					break;
				
				case "4.4.0":
					$this->getDirectDataFromQuery("SELECT id, title, description,".
						" startpage start_page, short, rating, rating_overall, introduction,". // imp_pages,
						" public_notes, page_toc, rating_side, rating_new, rating_ext".
						" FROM il_wiki_data JOIN object_data ON (il_wiki_data.id = object_data.obj_id)".
						" WHERE ".$ilDB->in("id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "wpg")
		{
			switch ($a_version)
			{
				case "4.1.0":
					$this->getDirectDataFromQuery("SELECT id, title, wiki_id".
						" FROM il_wiki_page".
						" WHERE ".$ilDB->in("wiki_id", $a_ids, false, "integer"));
					break;
				
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id, title, wiki_id,".
						" blocked, rating". 
						" FROM il_wiki_page".
						" WHERE ".$ilDB->in("wiki_id", $a_ids, false, "integer"));
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
			case "wiki":
				return array (
					"wpg" => array("ids" => $a_rec["Id"])
				);
		}

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
			case "wiki":
				
				include_once("./Modules/Wiki/classes/class.ilObjWiki.php");
				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjWiki();
					$newObj->setType("wiki");
					$newObj->create(true);
				}
					
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setShortTitle($a_rec["Short"]);
				$newObj->setStartPage($a_rec["StartPage"]);
				$newObj->setRatingOverall($a_rec["RatingOverall"]);
				$newObj->setRating($a_rec["Rating"]);
				$newObj->setIntroduction($a_rec["Introduction"]);
				$newObj->setPublicNotes($a_rec["PublicNotes"]);
				
				// >= 4.3
				if(isset($a_rec["PageToc"]))
				{
					// $newObj->setImportantPages($a_rec["ImpPages"]);
					$newObj->setPageToc($a_rec["PageToc"]);
					$newObj->setRatingAsBlock($a_rec["RatingSide"]);
					$newObj->setRatingForNewPages($a_rec["RatingNew"]);
					$newObj->setRatingCategories($a_rec["RatingExt"]);			
				}
				
				$newObj->update(true);
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/Wiki", "wiki", $a_rec["Id"], $newObj->getId());
				$a_mapping->addMapping("Services/Rating", "rating_category_parent_id", $a_rec["Id"], $newObj->getId());
				break;

			case "wpg":
				$wiki_id = $a_mapping->getMapping("Modules/Wiki", "wiki", $a_rec["WikiId"]);
				include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
				$wpage = new ilWikiPage();
				$wpage->setWikiId($wiki_id);
				$wpage->setTitle($a_rec["Title"]);
				
				// >= 4.3
				if(isset($a_rec["Blocked"]))
				{
					$wpage->setBlocked($a_rec["Blocked"]);
					$wpage->setRating($a_rec["Rating"]);
				}
				
				$wpage->create(true);
				
				$a_mapping->addMapping("Modules/Wiki", "wpg", $a_rec["Id"], $wpage->getId());
				$a_mapping->addMapping("Services/COPage", "pg", "wpg:".$a_rec["Id"], "wpg:".$wpage->getId());
				break;
		}
	}
}
?>