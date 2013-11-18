<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Bookmarks Data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesBookmarks
 */
class ilBookmarkDataSet extends ilDataSet
{	
	/**
	 * Get supported versions
	 *
	 * @param
	 * @return
	 */
	public function getSupportedVersions()
	{
		return array("4.3.0");
	}
	
	/**
	 * Get xml namespace
	 *
	 * @param
	 * @return
	 */
	function getXmlNamespace($a_entity, $a_schema_version)
	{
		return "http://www.ilias.de/xml/Services/Bookmarks/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		// bookmarks
		if ($a_entity == "bookmarks")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"UserId" => "integer"
					);
			}
		}
	
		// bookmark_tree
		if ($a_entity == "bookmark_tree")
		{
			switch ($a_version)
			{
				case "4.3.0":
						return array(
							"UserId" => "integer",
							"Child" => "integer",
							"Parent" => "integer",
							"Depth" => "integer",
							"Type" => "text",
							"Title" => "text",
							"Description" => "text",
							"Target" => "text"
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
				
		// bookmarks
		if ($a_entity == "bookmarks")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->data = array();
					foreach ($a_ids as $id)
					{
						$this->data[] = array("UserId" => $id);
					}
					break;
			}
		}	

		// bookmark_tree
		if ($a_entity == "bookmark_tree")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT tree user_id, child ".
						" ,parent,depth,type,title,description,target ".
						" FROM bookmark_tree JOIN bookmark_data ON (child = obj_id) ".
						" WHERE ".
						$ilDB->in("tree", $a_ids, false, "integer").
						" ORDER BY tree, depth");
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
			case "bookmarks":
				return array (
					"bookmark_tree" => array("ids" => $a_rec["UserId"])
				);							
		}
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
		switch ($a_entity)
		{
			case "bookmarks":
				break;

			case "bookmark_tree":
				$usr_id = $a_mapping->getMapping("Services/User", "usr", $a_rec["UserId"]);
				if ($usr_id > 0 && ilObject::_lookupType($usr_id) == "usr")
				{
//echo "<br><br>";
//var_dump($a_rec);
					switch ($a_rec["Type"])
					{
						case "bmf":
							if ($a_rec["Parent"] > 0)
							{
								$parent = (int) $a_mapping->getMapping("Services/Bookmarks", "bookmark_tree", $a_rec["Parent"]);
								include_once("./Services/Bookmarks/classes/class.ilBookmarkFolder.php");
								$bmf = new ilBookmarkFolder(0, $usr_id);
								$bmf->setTitle($a_rec["Title"]);
								$bmf->setParent($parent);
								$bmf->create();
								$fold_id = $bmf->getId();
							}
							else
							{
								$tree = new ilTree($usr_id);
								$tree->setTableNames('bookmark_tree','bookmark_data');
								$fold_id = $tree->readRootId();
							}
							$a_mapping->addMapping("Services/Bookmarks", "bookmark_tree", $a_rec["Child"],
								$fold_id);
							break;
	
						case "bm":
							$parent = 0;
							if (((int) $a_rec["Parent"]) > 0)
							{
								$parent = (int) $a_mapping->getMapping("Services/Bookmarks", "bookmark_tree", $a_rec["Parent"]);
							}
							else
							{
								return;
							}
							
							if ($parent == 0)
							{
								$tree = new ilTree($usr_id);
								$tree->setTableNames('bookmark_tree','bookmark_data');
								$parent = $tree->readRootId();								
							}
//echo "-$parent-";
							include_once("./Services/Bookmarks/classes/class.ilBookmark.php");
							$bm = new ilBookmark(0, $usr_id);
							$bm->setTitle($a_rec["Title"]);
							$bm->setDescription($a_rec["Description"]);
							$bm->setTarget($a_rec["Target"]);
							$bm->setParent($parent);
							$bm->create();
							break;
	
					}
				}
				break;
		}
	}
}
?>