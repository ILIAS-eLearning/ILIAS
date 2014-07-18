<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Taxonomy node
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesTaxonomy
 */
class ilTaxonomyNode
{
	var $type;
	var $id;
	var $title;

	/**
	 * Constructor
	 * @access	public
	 */
	function __construct($a_id = 0)
	{
		$this->id = $a_id;
		
//		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
//		$this->taxonomy_tree = new ilTaxonomyTree();

		if($a_id != 0)
		{
			$this->read();
		}

		$this->setType("taxn");
	}

	/**
	 * Set title
	 *
	 * @param	string		$a_title	title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get title
	 *
	 * @return	string		title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set type
	 *
	 * @param	string		Type
	 */
	function setType($a_type)
	{
		$this->type = $a_type;
	}

	/**
	 * Get type
	 *
	 * @return	string		Type
	 */
	function getType()
	{
		return $this->type;
	}

	/**
	 * Set Node ID
	 *
	 * @param	int		Node ID
	 */
	function setId($a_id)
	{
		$this->id = $a_id;
	}

	/**
	 * Get Node ID
	 *
	 * @param	int		Node ID
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set order nr
	 *
	 * @param int $a_val order nr	
	 */
	function setOrderNr($a_val)
	{
		$this->order_nr = $a_val;
	}
	
	/**
	 * Get order nr
	 *
	 * @return int order nr
	 */
	function getOrderNr()
	{
		return $this->order_nr;
	}

	/**
	 * Set taxonomy id
	 *
	 * @param int $a_val taxonomy id	
	 */
	function setTaxonomyId($a_val)
	{
		$this->taxonomy_id = $a_val;
	}
	
	/**
	 * Get taxonomy id
	 *
	 * @return int taxonomy id
	 */
	function getTaxonomyId()
	{
		return $this->taxonomy_id;
	}
	
	/**
	 * Read data from database
	 */
	function read()
	{
		global $ilDB;

		if(!isset($this->data_record))
		{
			$query = "SELECT * FROM tax_node WHERE obj_id = ".
				$ilDB->quote($this->id, "integer");
			$obj_set = $ilDB->query($query);
			$this->data_record = $ilDB->fetchAssoc($obj_set);
		}
		$this->setType($this->data_record["type"]);
		$this->setTitle($this->data_record["title"]);
		$this->setOrderNr($this->data_record["order_nr"]);
		$this->setTaxonomyId($this->data_record["tax_id"]);
	}

	/**
	 * Create taxonomy node
	 */
	function create()
	{
		global $ilDB;
		
		if ($this->getTaxonomyId() <= 0)
		{
			die("ilTaxonomyNode->create: No taxonomy ID given");
		}

		// insert object data
		$id = $ilDB->nextId("tax_node");
		$query = "INSERT INTO tax_node (obj_id, title, type, create_date, order_nr, tax_id) ".
			"VALUES (".
			$ilDB->quote($id, "integer").",".
			$ilDB->quote($this->getTitle(), "text").",".
			$ilDB->quote($this->getType(), "text").", ".
			$ilDB->now().", ".
			$ilDB->quote((int) $this->getOrderNr(), "integer").", ".
			$ilDB->quote((int) $this->getTaxonomyId(), "integer").
			")";
		$ilDB->manipulate($query);
		$this->setId($id);

	}

	/**
	 * Update Node
	 */
	function update()
	{
		global $ilDB;

		$query = "UPDATE tax_node SET ".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			" ,order_nr = ".$ilDB->quote((int) $this->getOrderNr(), "integer").
			" WHERE obj_id = ".$ilDB->quote($this->getId(), "integer");

		$ilDB->manipulate($query);
	}

	/**
	 * Delete taxonomy node
	 */
	function delete()
	{
		global $ilDB;
		
		// delete all assignments of the node
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		ilTaxNodeAssignment::deleteAllAssignmentsOfNode($this->getId());
		
		$query = "DELETE FROM tax_node WHERE obj_id= ".
			$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
	}

	/**
	 * Copy taxonomy node
	 */
	function copy($a_tax_id = 0)
	{
		$taxn = new ilTaxonomyNode();
		$taxn->setTitle($this->getTitle());
		$taxn->setType($this->getType());
		$taxn->setOrderNr($this->getOrderNr());
		if ($a_tax_id == 0)
		{
			$taxn->setTaxonomyId($this->getTaxonomyId());
		}
		else
		{
			$taxn->setTaxonomyId($a_tax_id);
		}

		$taxn->create();

		return $taxn;
	}

	/**
	 * Lookup
	 *
	 * @param	int			Node ID
	 */
	protected static function _lookup($a_obj_id, $a_field)
	{
		global $ilDB;

		$query = "SELECT $a_field FROM tax_node WHERE obj_id = ".
			$ilDB->quote($a_obj_id, "integer");
		$obj_set = $ilDB->query($query);
		$obj_rec = $ilDB->fetchAssoc($obj_set);

		return $obj_rec[$a_field];
	}

	/**
	 * Lookup Title
	 *
	 * @param	int			node ID
	 * @return	string		title
	 */
	static function _lookupTitle($a_obj_id)
	{
		global $ilDB;

		return self::_lookup($a_obj_id, "title");
	}

	/**
	 * Put this node into the taxonomy tree
	 */
	static function putInTree($a_tax_id, $a_node, $a_parent_id = "", $a_target_node_id = "",
		$a_order_nr = 0)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($a_tax_id);

		// determine parent
		$parent_id = ($a_parent_id != "")
			? $a_parent_id
			: $tax_tree->readRootId();

		// determine target
		if ($a_target_node_id != "")
		{
			$target = $a_target_node_id;
		}
		else
		{
			// determine last child that serves as predecessor
			$childs = $tax_tree->getChilds($parent_id);

			if (count($childs) == 0)
			{
				$target = IL_FIRST_NODE;
			}
			else
			{
				$target = $childs[count($childs) - 1]["obj_id"];
			}
		}

		if ($tax_tree->isInTree($parent_id) && !$tax_tree->isInTree($a_node->getId()))
		{
			$tax_tree->insertNode($a_node->getId(), $parent_id, $target);
		}
	}

	/**
	 * Write order nr
	 *
	 * @param
	 * @return
	 */
	static function writeOrderNr($a_node_id, $a_order_nr)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE tax_node SET ".
			" order_nr = ".$ilDB->quote($a_order_nr, "integer").
			" WHERE obj_id = ".$ilDB->quote($a_node_id, "integer")
			);
	}
	
	/**
	 * Write title
	 *
	 * @param
	 * @return
	 */
	static function writeTitle($a_node_id, $a_title)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE tax_node SET ".
			" title = ".$ilDB->quote($a_title, "text").
			" WHERE obj_id = ".$ilDB->quote($a_node_id, "integer")
			);
	}
	
	/**
	 * Put this node into the taxonomy tree
	 */
	static function getNextOrderNr($a_tax_id, $a_parent_id)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($a_tax_id);
		if ($a_parent_id == 0)
		{
			$a_parent_id = $tax_tree->readRootId();
		}
		$childs = $tax_tree->getChilds($a_parent_id);
		$max = 0;

		foreach ($childs as $c)
		{
			if ($c["order_nr"] > $max)
			{
				$max = $c["order_nr"] + 10;
			}
		}

		return $max;
	}
	
	/**
	 * Fix order numbers
	 *
	 * @param
	 * @return
	 */
	static function fixOrderNumbers($a_tax_id, $a_parent_id)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($a_tax_id);
		if ($a_parent_id == 0)
		{
			$a_parent_id = $tax_tree->readRootId();
		}
		$childs = $tax_tree->getChilds($a_parent_id);
		$childs = ilUtil::sortArray($childs, "order_nr", "asc", true);

		$cnt = 10;
		foreach ($childs as $c)
		{
			self::writeOrderNr($c["child"], $cnt);
			$cnt += 10;
		}
	}
	
}
?>
