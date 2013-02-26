<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Item group data set class
 * 
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ModulesItemGroup
 */
class ilItemGroupDataSet extends ilDataSet
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
		return "http://www.ilias.de/xml/Modules/ItemGroup/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		if ($a_entity == "itgr")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text");
			}
		}

		if ($a_entity == "itgr_item")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"ItemGroupId" => "integer",
						"ItemId" => "text"
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
				
		if ($a_entity == "itgr")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT obj_id id, title, description ".
						" FROM object_data ".
						"WHERE ".
						$ilDB->in("obj_id", $a_ids, false, "integer"));
					break;
			}
		}

		if ($a_entity == "itgr_item")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery($q = "SELECT item_group_id itgr_id, item_ref_id item_id".
						" FROM item_group_item ".
						"WHERE ".
						$ilDB->in("item_group_id", $a_ids, false, "integer"));
					break;
			}
		}

	}
	
	/**
	 * Get xml record (export)
	 *
	 * @param	array	abstract data record
	 * @return	array	xml record
	 */
	function getXmlRecord($a_entity, $a_version, $a_set)
	{
		if ($a_entity == "itgr_item")
		{
			// make ref id an object id
			$a_set["ItemId"] = ilObject::_lookupObjId($a_set["ItemId"]);
		}
		return $a_set;
	}

	/**
	 * Determine the dependent sets of data 
	 */
	protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
	{
		switch ($a_entity)
		{
			case "itgr":
				return array (
					"itgr_item" => array("ids" => $a_rec["Id"])
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
		global $ilLog;
		
//echo $a_entity;
//var_dump($a_rec);

		switch ($a_entity)
		{
			case "itgr":
				include_once("./Modules/ItemGroup/classes/class.ilObjItemGroup.php");
				
				if ($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
				{
					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
				}
				else
				{
					$newObj = new ilObjItemGroup();
					$newObj->setType("itgr");
					$newObj->create(true);
				}
				
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->update(true);
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Modules/ItemGroup", "itgr", $a_rec["Id"], $newObj->getId());
				
				break;
				
			case "itgr_item":
				if($obj_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['ItemId']))
				{
					$ref_id = current(ilObject::_getAllReferences($obj_id));
					include_once './Modules/ItemGroup/classes/class.ilItemGroupItems.php';
					$itgri = new ilItemGroupItems();
					$itgri->setItemGroupId($this->current_obj->getId());
					$itgri->read();
					$itgri->addItem($ref_id);
//$ilLog->write("Adding item with ref id -".$ref_id."- to group with id -".$this->current_obj->getId()."-.");
					$itgri->update();
				}
				break;

		}
	}
}
?>