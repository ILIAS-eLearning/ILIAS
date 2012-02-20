<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for taxonomies
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTaxonomyTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_tree,
		$a_node_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		if ($a_node_id == "")
		{
			$a_node_id = $a_tree->readRootId();
		}
		
		$this->tree = $a_tree;
		$this->node_id = $a_node_id;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$childs = $this->tree->getChildsByTypeFilter($a_node_id,
			array("taxn"));
		//$childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
		$childs = ilUtil::sortArray($childs, "title", "asc", false);
		$this->setData($childs);
		
		$this->setTitle($lng->txt("tax_nodes"));
		
		$this->addColumn($this->lng->txt(""), "", "1px", true);
		$this->addColumn($this->lng->txt("type"), "", "1px");
		$this->addColumn($this->lng->txt("tax_order"), "", "1px");
		$this->addColumn($this->lng->txt("title"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.tax_row.html", "Services/Taxonomy");

		$this->addMultiCommand("deleteItems", $lng->txt("delete"));
		$this->addMultiCommand("cutItems", $lng->txt("cut"));
		$this->addMultiCommand("copyItems", $lng->txt("copy"));
//		$this->addCommandButton("saveOrder", $lng->txt("tax_save_order"));

	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$ilCtrl->setParameter($this->parent_obj, "tax_node", $a_set["child"]);
		$ret = $ilCtrl->getLinkTargetByClass("ilobjtaxonomygui", "listItems");
		$ilCtrl->setParameter($this->parent_obj, "tax_node", $_GET["tax_node"]);

		$this->tpl->setVariable("HREF_TITLE", $ret);
		
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("NODE_ID", $a_set["child"]);
		//$this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);
		//$this->tpl->setVariable("ICON", ilUtil::img($icon, ""));
	}

}
?>
