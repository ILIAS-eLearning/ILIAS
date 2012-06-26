<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Notes Data set class. Entities
 * - user_notes: All personal notes of a user (do not use this for object
 *               related queries. Add a new entity for this purpose.
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesNotes
 */
class ilNotesDataSet extends ilDataSet
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
		return "http://www.ilias.de/xml/Services/Notes/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		// user notes
		if ($a_entity == "user_notes")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"Id" => "integer",
						"RepObjId" => "integer",
						"ObjId" => "integer",
						"ObjType" => "text",
						"ObjType" => "text",
						"Type" => "integer",
						"Author" => "integer",
						"CreationDate" => "timestamp",
						"NoteText" => "text",
						"Label" => "integer",
						"Subject" => "text",
						"NoRepository" => "integer"
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

		// user notes
		if ($a_entity == "user_notes")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id, rep_obj_id, obj_id, obj_type, type, ".
						" author, note_text, creation_date, label, subject, no_repository ".
						" FROM note ".
						" WHERE ".
						$ilDB->in("author", $a_ids, false, "integer").
						" AND obj_type = ".$ilDB->quote("pd" ,"text"));
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
return;
//echo $a_entity;
//var_dump($a_rec);

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

			case "mep_tree":
				switch ($a_rec["Type"])
				{
					case "fold":
						$parent = (int) $a_mapping->getMapping("Modules/MediaPool", "mep_tree", $a_rec["Parent"]);
						$fold_id =
							$this->current_obj->createFolder($a_rec["Title"], $parent);
						$a_mapping->addMapping("Modules/MediaPool", "mep_tree", $a_rec["Child"],
							$fold_id);
						break;

					case "mob":
						$parent = (int) $a_mapping->getMapping("Modules/MediaPool", "mep_tree", $a_rec["Parent"]);
						$mob_id = (int) $a_mapping->getMapping("Services/MediaObjects", "mob", $a_rec["ForeignId"]);
						$item = new ilMediaPoolItem();
						$item->setType("mob");
						$item->setForeignId($mob_id);
						$item->setTitle($a_rec["Title"]);
						$item->create();
						if ($item->getId() > 0)
						{
							$this->current_obj->insertInTree($item->getId(), $parent);
						}
						break;

					case "pg":
						$parent = (int) $a_mapping->getMapping("Modules/MediaPool", "mep_tree", $a_rec["Parent"]);

						$item = new ilMediaPoolItem();
						$item->setType("pg");
						$item->setTitle($a_rec["Title"]);
						$item->create();
						$a_mapping->addMapping("Services/COPage", "pg", "mep:".$a_rec["Child"],
							"mep:".$item->getId());
						if ($item->getId() > 0)
						{
							$this->current_obj->insertInTree($item->getId(), $parent);
						}
						break;

				}
		}
	}
}
?>