<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Taxonomy/exceptions/class.ilTaxonomyException.php");

/**
 * Taxonomy node <-> item assignment
 *
 * This class allows to assign items to taxonomy nodes.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services/Taxonomy
 */
class ilTaxNodeAssignment
{
	/**
	 * Constructor
	 *
	 * @param string $a_component_id component id (e.g. "glo" for Modules/Glossary)
	 * @param string $a_item_type item type (e.g. "term", must be unique component wide)
	 * @param int $a_tax_id taxonomy id
	 */
	function __construct($a_component_id, $a_item_type, $a_tax_id)
	{
		if ($a_component_id == "")
		{
			throw new ilTaxonomyException('No component ID passed to ilTaxNodeAssignment.');
		}
		
		if ($a_item_type == "")
		{
			throw new ilTaxonomyException('No item type passed to ilTaxNodeAssignment.');
		}

		if ((int) $a_tax_id == 0)
		{
			throw new ilTaxonomyException('No taxonomy ID passed to ilTaxNodeAssignment.');
		}

		$this->setComponentId($a_component_id);
		$this->setItemType($a_item_type);
		$this->setTaxonomyId($a_tax_id);
	}
	
	/**
	 * Set component id
	 *
	 * @param string $a_val component id	
	 */
	protected function setComponentId($a_val)
	{
		$this->component_id = $a_val;
	}
	
	/**
	 * Get component id
	 *
	 * @return string component id
	 */
	public function getComponentId()
	{
		return $this->component_id;
	}
	
	/**
	 * Set item type
	 *
	 * @param string $a_val item type
	 */
	protected function setItemType($a_val)
	{
		$this->item_type = $a_val;
	}
	
	/**
	 * Get item type
	 *
	 * @return string item type
	 */
	public function getItemType()
	{
		return $this->item_type;
	}
	
	/**
	 * Set taxonomy id
	 *
	 * @param int $a_val taxonomy id	
	 */
	protected function setTaxonomyId($a_val)
	{
		$this->taxonomy_id = $a_val;
	}
	
	/**
	 * Get taxonomy id
	 *
	 * @return int taxonomy id
	 */
	public function getTaxonomyId()
	{
		return $this->taxonomy_id;
	}
	
	/**
	 * Get assignments of node
	 *
	 * @param int $a_node_id node id
	 * @return array array of tax node assignments arrays
	 */
	final public function getAssignmentsOfNode($a_node_id)
	{
		global $ilDB;
		
		if (is_array($a_node_id))
		{
			$set = $ilDB->query("SELECT * FROM tax_node_assignment ".
				" WHERE ".$ilDB->in("node_id", $a_node_id, false, "integer")
				);
		}
		else
		{
			$set = $ilDB->query("SELECT * FROM tax_node_assignment ".
				" WHERE node_id = ".$ilDB->quote($a_node_id, "integer")
				);
		}
		$ass = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ass[] = $rec;
		}
		
		return $ass;
	}
	
	/**
	 * Get assignments for item
	 *
	 * @param int $a_item_id item id
	 * @return array array of tax node assignments arrays
	 */
	final public function getAssignmentsOfItem($a_item_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM tax_node_assignment ".
			" WHERE component = ".$ilDB->quote($this->getComponentId(), "text").
			" AND item_type = ".$ilDB->quote($this->getItemType(), "text").
			" AND item_id = ".$ilDB->quote($a_item_id, "integer")
			);
		$ass = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ass[] = $rec;
		}
		return $ass;
	}
	
	/**
	 * Add assignment
	 *
	 * @param int $a_node_id node id
	 * @param int $a_item_id item id
	 */
	function addAssignment($a_node_id, $a_item_id)
	{
		global $ilDB;
		
		// nothing to do, if not both IDs are greater 0
		if ((int) $a_node_id == 0 || (int) $a_item_id == 0)
		{
			return;
		}
		
		// sanity check: does the node belong to the given taxonomy?
		$set = $ilDB->query("SELECT tax_tree_id FROM tax_tree ".
			" WHERE child = ".$ilDB->quote($a_node_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		if ($rec["tax_tree_id"] != $this->getTaxonomyId())
		{
			throw new ilTaxonomyException('addAssignment: Node ID does not belong to current taxonomy.');
		}
		
		$ilDB->replace("tax_node_assignment",
			array(
				"node_id" => array("integer", $a_node_id),
				"component" => array("text", $this->getComponentId()),
				"item_type" => array("text", $this->getItemType()),
				"item_id" => array("integer", $a_item_id)
				),
			array()
			);
	}
	
	/**
	 * Delete assignments of item
	 *
	 * @param int $a_item_id item id
	 */
	function deleteAssignmentsOfItem($a_item_id)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM tax_node_assignment WHERE ".
			" component = ".$ilDB->quote($this->getComponentId(), "text").
			" AND item_type = ".$ilDB->quote($this->getItemType(), "text").
			" AND item_id = ".$ilDB->quote($a_item_id, "integer")
			);
	}

	/**
	 * Delete assignments of node
	 *
	 * @param int $a_node_id node id
	 */
	static function deleteAssignmentsOfNode($a_node_id)
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM tax_node_assignment WHERE ".
			" node_id = ".$ilDB->quote($a_node_id, "integer"));
	}
	
}

?>
