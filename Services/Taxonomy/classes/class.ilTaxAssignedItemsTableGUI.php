<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for taxonomy list
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTaxAssignedItemsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_node_id, $a_tax, $a_comp_id, $a_obj_id, $a_item_type,
		$a_info_obj)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;
		
		$this->setId("tax_ass_it");
		$this->setLimit(9999);
		$this->tax = $a_tax;
		$this->node_id = $a_node_id;
		$this->comp_id = $a_comp_id;
		$this->obj_id = $a_obj_id;
		$this->item_type = $a_item_type;
		$this->info_obj = $a_info_obj;

		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		include_once("./Services/Taxonomy/classes/class.ilObjTaxonomy.php");
		
		include_once("./Services/Taxonomy/classes/class.ilTaxNodeAssignment.php");
		$tax_ass = new ilTaxNodeAssignment($this->comp_id, $this->obj_id, $this->item_type, $this->tax->getId());
		$this->setData($tax_ass->getAssignmentsOfNode($this->node_id));
		$this->setTitle($lng->txt("tax_assigned_items"));
		
		$this->addColumn($this->lng->txt("tax_order"));
		$this->setDefaultOrderField("order_nr");
		$this->setDefaultOrderDirection("asc");

		$this->addColumn($this->lng->txt("title"));
		
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.tax_ass_items_row.html", "Services/Taxonomy");
		$this->addCommandButton("saveAssignedItemsSorting", $lng->txt("save"));
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	function numericOrdering($a_field)
	{
		if (in_array($a_field, array("order_nr")))
		{
			return true;
		}
		return false;
	}


	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		$this->tpl->setVariable("ONODE_ID", $a_set["item_id"]);
		$this->tpl->setVariable("ORDER_NR", (int) $a_set["order_nr"]);
		$this->tpl->setVariable("TITLE", $this->info_obj->getTitle(
			$a_set["component"], $a_set["item_type"], $a_set["item_id"]));
	}

}
?>
