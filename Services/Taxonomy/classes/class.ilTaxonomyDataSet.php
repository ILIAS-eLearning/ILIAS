<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/DataSet/classes/class.ilDataSet.php");

/**
 * Taxonomy data set class
 * 
 * This class implements the following entities:
 * - tax: data from table tax_data/object_data
 * - tax_usage: data from table tax_usage
 * - tax_tree: data from a join on tax_tree and tax_node
 * - tax_node_assignment: data from table tax_node_assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ingroup ServicesTaxononmy
 */
class ilTaxonomyDataSet extends ilDataSet
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
		return "http://www.ilias.de/xml/Services/Taxonomy/".$a_entity;
	}
	
	/**
	 * Get field types for entity
	 *
	 * @param
	 * @return
	 */
	protected function getTypes($a_entity, $a_version)
	{
		// tax
		if ($a_entity == "tax")
		{
			switch ($a_version)
			{
				case "4.3.0":
					return array(
						"Id" => "integer",
						"Title" => "text",
						"Description" => "text",
						"SortingMode" => "integer");
			}
		}
	
		// tax_usage
		if ($a_entity == "tax_usage")
		{
			switch ($a_version)
			{
				case "4.3.0":
						return array(
							"TaxId" => "integer",
							"ObjId" => "integer"
						);
			}
		}

		// tax_tree
		if ($a_entity == "tax_tree")
		{
			switch ($a_version)
			{
				case "4.3.0":
						return array(
							"TaxId" => "integer",
							"Child" => "integer",
							"Parent" => "integer",
							"Depth" => "integer",
							"Type" => "text",
							"Title" => "text",
							"OrderNr" => "integer"
						);
			}
		}				

		// tax_node_assignment
		if ($a_entity == "tax_node_assignment")
		{
			switch ($a_version)
			{
				case "4.3.0":
						return array(
							"NodeId" => "integer",
							"Component" => "text",
							"ItemType" => "text",
							"ItemId" => "integer"
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
				
		// tax
		if ($a_entity == "tax")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT id, title, description, ".
						" sorting_mode".
						" FROM tax_data JOIN object_data ON (tax_data.id = object_data.obj_id) ".
						"WHERE ".
						$ilDB->in("id", $a_ids, false, "integer"));
					break;
			}
		}	

		// tax usage
		if ($a_entity == "tax_usage")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT tax_id, obj_id ".
						" FROM tax_usage ".
						"WHERE ".
						$ilDB->in("tax_id", $a_ids, false, "integer"));
					break;
			}
		}	

		// tax_tree
		if ($a_entity == "tax_tree")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT tax_id, child ".
						" ,parent,depth,type,title,order_nr ".
						" FROM tax_tree JOIN tax_node ON (child = obj_id) ".
						" WHERE ".
						$ilDB->in("tax_id", $a_ids, false, "integer").
						" ORDER BY depth");
					break;
			}
		}			

		// tax node assignment
		if ($a_entity == "tax_node_assignment")
		{
			switch ($a_version)
			{
				case "4.3.0":
					$this->getDirectDataFromQuery("SELECT node_id, component, item_type, item_id ".
						" FROM tax_node_assignment ".
						"WHERE ".
						$ilDB->in("node_id", $a_ids, false, "integer"));
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
			case "tax":
				return array (
					"tax_tree" => array("ids" => $a_rec["Id"]),
					"tax_usage" => array("ids" => $a_rec["Id"])
				);							
			case "tax_tree":
				return array (
					"tax_node_assignment" => array("ids" => $a_rec["Child"])
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
//echo $a_entity;
//var_dump($a_rec);

		switch ($a_entity)
		{
			case "tax":
				include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");

//				if($new_id = $a_mapping->getMapping('Services/Container','objs',$a_rec['Id']))
//				{
//					$newObj = ilObjectFactory::getInstanceByObjId($new_id,false);
//				}
//				else
//				{
					$newObj = new ilObjTaxonomy();
					$newObj->create();
//				}
				
				$newObj->setTitle($a_rec["Title"]);
				$newObj->setDescription($a_rec["Description"]);
				$newObj->setSortingMode($a_rec["SortingMode"]);
				$newObj->update();
				
				$this->current_obj = $newObj;
				$a_mapping->addMapping("Services/Taxonomy", "tax", $a_rec["Id"], $newObj->getId());
				break;

			case "tax_tree":
				switch ($a_rec["Type"])
				{
					case "taxn":
						$parent = (int) $a_mapping->getMapping("Services/Taxonomy", "tax_tree", $a_rec["Parent"]);
						$tax_id = $a_mapping->getMapping("Services/Taxonomy", "tax", $a_rec["TaxId"]);
						if ($parent == 0)
						{
							$parent = $this->current_obj->getTree()->readRootId();
						}
						$node = new ilTaxonomyNode();
						$node->setTitle($a_rec["Title"]);
						$node->setOrderNr($a_rec["OrderNr"]);
						$node->setTaxonomyId($tax_id);
						$node->create();
						ilTaxonomyNode::putInTree($tax_id, $node, (int) $parent, "", $a_rec["OrderNr"]);
						$a_mapping->addMapping("Services/Taxonomy", "tax_tree", $a_rec["Child"],
							$node->getId());
						break;
						
				}
				
			case "tax_node_assignment":
				$new_item_id = (int) $a_mapping->getMapping("Services/Taxonomy", "tax_item",
					$a_rec["Component"].":".$a_rec["ItemType"].":".$a_rec["ItemId"]);
				$new_node_id = (int) $a_mapping->getMapping("Services/Taxonomy", "tax_tree", $a_rec["NodeId"]);

				// this is needed since 4.4 (but not exported with 4.3)
				// with 4.4 this should be part of export/import
				$new_item_id_obj = (int) $a_mapping->getMapping("Services/Taxonomy", "tax_item_obj_id",
					$a_rec["Component"].":".$a_rec["ItemType"].":".$a_rec["ItemId"]);
				if ($new_item_id > 0 && $new_node_id > 0 && $new_item_id_obj  > 0)
				{
					include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
					$node_ass = new ilTaxNodeAssignment($a_rec["Component"], $new_item_id_obj, $a_rec["ItemType"], $this->current_obj->getId());
					$node_ass->addAssignment($new_node_id, $new_item_id);
				}
				break;
				
			case "tax_usage":
				$usage = $a_mapping->getMapping("Services/Taxonomy", "tax_usage_of_obj", $a_rec["ObjId"]);
				if ($usage != "")
				{
					$usage.=":";
				}
				$a_mapping->addMapping("Services/Taxonomy", "tax_usage_of_obj", $a_rec["ObjId"],
					$this->current_obj->getId());
				break;
		}
	}
}
?>