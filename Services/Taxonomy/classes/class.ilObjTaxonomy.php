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
	 * Create a new taxonomy
	 */
	function doCreate()
	{
		global $ilDB;
		
		// create the taxonomy tree
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($this->getId());
		$tax_tree->addTree($this->getId(), 1);
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
		

	}


	/**
	 * Delete taxonomy object
	 */
	function doDelete()
	{
		global $ilDB;
		
		// delete object
		
	}


	/**
	 * Read taxonomy properties
	 */
	function doRead()
	{
		global $ilDB;
		
		//
	}

	/**
	 * Upate taxonomy properties
	 */
	function doUpdate()
	{
		global $ilDB;
		
		//
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
	
}
?>
