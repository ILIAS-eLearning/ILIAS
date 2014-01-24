<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Blog Data set class
 * 
 * This class implements the following entities:
 * - blog: object data
 * - blog_posting: data from table il_blog_posting
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ingroup ModulesBlog
 */
class ilBlogDataSet extends ilDataSet
{	
	protected $current_blog;
	
	/**
	 * Get supported versions
	 */
	public function getSupportedVersions()
	{
		return array("4.3.0");
	}
	
	/**
	 * Get xml namespace
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Modules/Blog/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 */
	protected function getTypes($a_entity, $a_version)
	{		
		if ($a_entity == "blog")
		{
			switch ($a_version)
			{			
				case "4.3.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"Notes" => "integer",
						"BgColor" => "text",
						"FontColor" => "text",
						"Img" => "text",
						"Ppic" => "integer",
						"RssActive" => "integer",
						"Approval" => "integer",
						"Dir" => "directory"
						);
			}
		}
		
		if ($a_entity == "blog_posting")
		{
			switch ($a_version)
			{				
				case "4.3.0":
					return array(
						"Id" => "integer",
						"BlogId" => "integer",
						"Title" => "integer",
						"Created" => "text",
						"Author" => "text",
						"Approved" => "integer"						
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
		
		if ($a_entity == "blog")
		{
			switch ($a_version)
			{				
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT bl.id,od.title,od.description,".
						"bl.notes,bl.bg_color,bl.font_color,bl.img,bl.ppic,bl.rss_active,bl.approval".
						" FROM il_blog bl".
						" JOIN object_data od ON (od.obj_id = bl.id)".
						" WHERE ".$ilDB->in("bl.id", $a_ids, false, "integer").
						" AND od.type = ".$ilDB->quote("blog", "text"));
					break;				
			}
		}
		
		if ($a_entity == "blog_posting")
		{
			switch ($a_version)
			{				
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id,blog_id,title,created,author,approved".
						" FROM il_blog_posting WHERE ".
						$ilDB->in("blog_id", $a_ids, false, "integer"));
					foreach($this->data as $idx => $item)
					{						
						// create full export id
						$this->data[$idx]["Author"] = $this->createObjectExportId("usr", $item["Author"]);
					}
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
			case "blog":
				return array (
					"blog_posting" => array("ids" => $a_rec["Id"])
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
		if ($a_entity == "blog")
		{
			include_once("./Modules/Blog/classes/class.ilObjBlog.php");
			$dir = ilObjBlog::initStorage($a_set["Id"]);
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
	function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
	{
		switch ($a_entity)
		{
			case "blog":
				include_once("./Modules/Blog/classes/class.ilObjBlog.php");
				$newObj = new ilObjBlog();
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->create();
								
				$newObj->setNotesStatus($a_rec["Notes"]);
				$newObj->setBackgroundColor($a_rec["BgColor"]);
				$newObj->setFontColor($a_rec["FontColor"]);
				$newObj->setProfilePicture($a_rec["Ppic"]);
				$newObj->setRSS($a_rec["RssActive"]);
				$newObj->setApproval($a_rec["Approval"]);
				$newObj->setImage($a_rec["Img"]);
				$newObj->update();
				
				// handle image(s)
				if($a_rec["Img"])
				{								
					$dir = str_replace("..", "", $a_rec["Dir"]);										
					if ($dir != "" && $this->getImportDirectory() != "")
					{
						$source_dir = $this->getImportDirectory()."/".$dir;
						$target_dir = ilObjBlog::initStorage($newObj->getId());		
						ilUtil::rCopy($source_dir, $target_dir);
					}
				}

				$a_mapping->addMapping("Modules/Blog", "blog", $a_rec["Id"], $newObj->getId());
				break;

			case "blog_posting":							
				$blog_id = (int) $a_mapping->getMapping("Modules/Blog", "blog", $a_rec["BlogId"]);
				if($blog_id)
				{
					include_once("./Modules/Blog/classes/class.ilBlogPosting.php");
					$newObj = new ilBlogPosting();
					$newObj->setBlogId($blog_id);
					$newObj->setTitle($a_rec["Title"]);																					
					$newObj->setCreated(new ilDateTime($a_rec["Created"], IL_CAL_DATETIME));
					$newObj->setApproved($a_rec["Approved"]);		
					
					// parse export id into local id (if possible)
					$author = $this->parseObjectExportId($a_rec["Author"], -1);					
					$newObj->setAuthor($author["id"]);
					
					$newObj->create(true);
					
					$a_mapping->addMapping("Services/COPage", "pg", "blp:".$a_rec["Id"], "blp:".$newObj->getId());				
				}
				break;
		}
	}	
}

?>