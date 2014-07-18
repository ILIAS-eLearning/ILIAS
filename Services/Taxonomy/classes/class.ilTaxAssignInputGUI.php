<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
include_once("./Services/Taxonomy/exceptions/class.ilTaxonomyException.php");

/**
 * Input GUI class for taxonomy assignments
 *
 * @author Alex Killing <alex.killing@gmx.de> 
 * @version $Id$
 *
 * @ingroup	ServicesTaxonomy
 */
class ilTaxAssignInputGUI extends ilSelectInputGUI
{
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($a_taxonomy_id, $a_multi = true, $a_title = "", $a_postvar = "",
		$a_include_please_select = true)
	{
		global $lng;
		
		$lng->loadLanguageModule("tax");
		$this->setMulti($a_multi);
		$this->include_please_select = $a_include_please_select;
		
		if ($a_title == "")
		{
			$a_title = $lng->txt("tax_taxonomy");
		}
		
		if ($a_postvar == "")
		{
			$a_postvar = "tax_node_assign";
		}
		
		parent::__construct($a_title, $a_postvar);
		$this->setType("tax_assign");
		
		if ((int) $a_taxonomy_id == 0)
		{
			throw new ilTaxonomyExceptions("No taxonomy ID passed to ilTaxAssignInputGUI.");
		}
		
		$this->setTaxonomyId((int) $a_taxonomy_id);
	}
	
	/**
	 * Set taxonomy id
	 *
	 * @param int $a_val taxonomy id	
	 */
	function setTaxonomyId($a_val)
	{
		$this->taxononmy_id = $a_val;
	}
	
	/**
	 * Get taxonomy id
	 *
	 * @return int taxonomy id
	 */
	function getTaxonomyId()
	{
		return $this->taxononmy_id;
	}

	/**
	 * Set Options.
	 *
	 * @param	array	$a_options	Options. Array ("value" => "option_text")
	 */
	function setOptions($a_options)
	{
		throw new ilTaxonomyExceptions("setOptions: Not supported for ilTaxAssignInputGUI.");
	}

	/**
	 * Get Options.
	 *
	 * @return	array	Options. Array ("value" => "option_text")
	 */
	function getOptions()
	{
		global $lng;
		
		if ($this->include_please_select)
		{
			$options = array("" => $lng->txt("please_select"));
		}
		
		include_once("./Services/Taxonomy/classes/class.ilTaxonomyTree.php");
		$tax_tree = new ilTaxonomyTree($this->getTaxonomyId());
		
		$nodes = $tax_tree->getSubtree($tax_tree->getNodeData($tax_tree->readRootId()));
		foreach ($nodes as $n)
		{
			if ($n["type"] == "taxn")
			{
				$options[$n["child"]] = str_repeat("&nbsp;", ($n["depth"] - 2) * 2).$n["title"];
			}
		}
		
		return $options;
	}
	
	/**
	 * Save input
	 *
	 * @param
	 * @return
	 */
	function saveInput($a_component_id, $a_obj_id, $a_item_type, $a_item_id)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		$tax_node_ass = new ilTaxNodeAssignment($a_component_id, $a_obj_id, $a_item_type, $this->getTaxonomyId());
		//$tax_node_ass->deleteAssignmentsOfItem($a_item_id);

		$post = $_POST[$this->getPostVar()];
		if (!$this->getMulti())
		{
			$post = array($post);
		}

		$current_ass = $tax_node_ass->getAssignmentsOfItem($a_item_id);
		$exising = array();
		foreach ($current_ass as $ca)
		{
			if (!in_array($ca["node_id"], $post))
			{
				$tax_node_ass->deleteAssignment($ca["node_id"], $a_item_id);
			}
			else
			{
				$exising[] = $ca["node_id"];
			}
		}

		foreach ($post as $p)
		{
			if (!in_array($p, $exising))
			{
				$tax_node_ass->addAssignment(ilUtil::stripSlashes($p), $a_item_id);
			}
		}
	}
	
	/**
	 * Set current values
	 *
	 * @param
	 * @return
	 */
	function setCurrentValues($a_component_id, $a_obj_id, $a_item_type, $a_item_id)
	{
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		$tax_node_ass = new ilTaxNodeAssignment($a_component_id, $a_obj_id, $a_item_type, $this->getTaxonomyId());
		$ass = $tax_node_ass->getAssignmentsOfItem($a_item_id);
		
		$nodes = array();
		foreach ($ass as $a)
		{
			$nodes[] = $a["node_id"];
		}
		if ($this->getMulti())
		{
			$this->setValue($nodes);
		}
		else
		{
			$this->setValue($nodes[0]);
		}
	}

}
