<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2.php";

/**
 * Taxonomy
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * $Id$
 *
 */
class ilObjTaxonomy extends ilObject2
{
	const SORT_ALPHABETICAL = 0;
	const SORT_MANUAL = 1;
	protected $node_mapping = array();
	
	/**
	 * Constructor
	 *
	 * @param	integer	object id
	 */
	function ilObjTaxonomy($a_id = 0)
	{
		$this->type = "tax";
		parent::ilObject($a_id, false);
	}

	/**
	 * Init type
	 *
	 * @param
	 * @return
	 */
	function initType()
	{
		$this->type = "tax";
	}
	
	/**
	 * Set sorting mode
	 *
	 * @param int $a_val sorting mode	
	 */
	function setSortingMode($a_val)
	{
		$this->sorting_mode = $a_val;
	}
	
	/**
	 * Get sorting mode
	 *
	 * @return int sorting mode
	 */
	function getSortingMode()
	{
		return $this->sorting_mode;
	}
	
	/**
	 * Get tree
	 *
	 * @param
	 * @return
	 */
	function getTree()
	{
		if ($this->getId() > 0)
		{
			include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
			$tax_tree = new ilTaxonomyTree($this->getId());
			return $tax_tree;
		}
		return false;
	}
	
	/**
	 * Get node mapping (used after cloning)
	 *
	 * @param
	 * @return
	 */
	function getNodeMapping()
	{
		return $this->node_mapping;
	}
	
	
	/**
	 * Create a new taxonomy
	 */
	function doCreate()
	{
		global $ilDB;
		
		// create tax data record
		$ilDB->manipulate("INSERT INTO tax_data ".
			"(id, sorting_mode) VALUES (".
			$ilDB->quote($this->getId(), "integer").",".
			$ilDB->quote((int) $this->getSortingMode(), "integer").
			")");
		
		// create the taxonomy tree
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		$node = new ilTaxonomyNode();
		$node->setType("");	// empty type
		$node->setTitle("Root node for taxonomy ".$this->getId());
		$node->setTaxonomyId($this->getId());
		$node->create();
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($this->getId());
		$tax_tree->addTree($this->getId(), $node->getId());
	}
	
	/**
	 * clone taxonomy sheet (note: taxonomies have no ref ids and return an object id)
	 * 
 	 * @access	public
	 * @return	integer		new obj id
	 */
	function doCloneObject($a_new_obj, $a_target_id, $a_copy_id)
	{
		global $log, $lng;
		
		$a_new_obj->setTitle($this->getTitle());
		$a_new_obj->setDescription($this->getDescription());
		$a_new_obj->setSortingMode($this->getSortingMode());

		$this->node_mapping = array();
		
		$this->cloneNodes($a_new_obj, $a_new_obj->getTree()->readRootId(),
			$this->getTree()->readRootId());
	}

	/**
	 * Clone nodes
	 *
	 * @param
	 * @return
	 */
	function cloneNodes($a_new_obj, $a_target_parent, $a_source_parent)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		
		// get all childs
		$nodes = $this->getTree()->getChilds($a_source_parent);
		foreach ($nodes as $node)
		{
			switch ($node["type"])
			{
				case "taxn":
					$tax_node = new ilTaxonomyNode($node["child"]);
					$new_node = $tax_node->copy($a_new_obj->getId());
					break;
			}

			ilTaxonomyNode::putInTree($a_new_obj->getId(),
				$new_node, $a_target_parent);
			
			$this->node_mapping[$node["child"]] = $new_node->getId();

			// handle childs
			$this->cloneNodes($a_new_obj, $new_node->getId(), $node["child"]);
		}

	}
	

	/**
	 * Delete taxonomy object
	 */
	function doDelete()
	{
		global $ilDB;

		// delete usages
		self::deleteUsagesOfTaxonomy($this->getId());

		// get all nodes
		$tree = $this->getTree();
		$subtree = $tree->getSubTreeIds($tree->readRootId());
		$subtree[] = $tree->readRootId();
		
		// get root node data (important: must happen before we
		// delete the nodes
		$root_node_data = $tree->getNodeData($tree->readRootId());
		
		// delete all nodes
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyNode.php");
		foreach ($subtree as $node_id)
		{
			// delete node (this also deletes its assignments)
			$node = new ilTaxonomyNode($node_id);
			$node->delete();
		}
		
		// delete the tree
		$tree->deleteTree($root_node_data);

		// delete taxonoymy properties record
		$ilDB->manipulate("DELETE FROM tax_data WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		
	}


	/**
	 * Read taxonomy properties
	 */
	function doRead()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM tax_data ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		$rec  = $ilDB->fetchAssoc($set);
		$this->setSortingMode($rec["sorting_mode"]);
	}

	/**
	 * Upate taxonomy properties
	 */
	function doUpdate()
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE tax_data SET ".
			" sorting_mode = ".$ilDB->quote((int) $this->getSortingMode(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
	}
	
	/**
	 * Load language module
	 *
	 * @param
	 * @return
	 */
	static function loadLanguageModule()
	{
		global $lng;
		
		$lng->loadLanguageModule("tax");
	}
	
	
	/**
	 * Save Usage
	 *
	 * @param
	 * @return
	 */
	static function saveUsage($a_tax_id, $a_obj_id)
	{
		global $ilDB;
		
		$ilDB->replace("tax_usage",
			array("tax_id" => array("integer", $a_tax_id),
				"obj_id" => array("integer", $a_obj_id)
				),
			array()
			);
	}
	
	/**
	 * Get usage of object
	 *
	 * @param int $a_obj_id object id
	 * @return array array of taxonomies
	 */
	static function getUsageOfObject($a_obj_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT tax_id FROM tax_usage ".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer")
			);
		$tax = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$tax[] = $rec["tax_id"];
		}
		return $tax;
	}
	
	/**
	 * Delete all usages of a taxonomy
	 *
	 * @param
	 * @return
	 */
	static function deleteUsagesOfTaxonomy($a_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM tax_usage WHERE ".
			" tax_id = ".$ilDB->quote($a_id, "integer")
			);
		
	}
	
	
	/**
	 * Get all assigned items under a node
	 *
	 * @param
	 * @return
	 */
	static function getSubTreeItems($a_tax_id, $a_node)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tree = new ilTaxonomyTree($a_tax_id);

		$sub_nodes = $tree->getSubTreeIds($a_node);
		$sub_nodes[] = $a_node;
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		$items = ilTaxNodeAssignment::getAssignmentsOfNode($sub_nodes);
		
		return $items;
	}
	
	/**
	 * Lookup
	 *
	 * @param
	 * @return
	 */
	static protected function lookup($a_field, $a_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_field." FROM tax_data ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);

		return $rec[$a_field];
	}
	
	/**
	 * Lookup sorting mode
	 *
	 * @param int $a_id taxonomy id
	 * @return int sorting mode
	 */
	public static function lookupSortingMode($a_id)
	{
		return self::lookup("sorting_mode", $a_id);
	}
}
?>
