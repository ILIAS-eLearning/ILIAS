<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Item group items table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesItemGroup
 */
class ilItemGroupItemsTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $lng, $ilCtrl, $tree, $objDefinition;
		
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->tree = $tree;
		$this->obj_def = $objDefinition;
		$this->parent_node = $this->tree->getNodeData(
			$this->tree->getParentId($a_parent_obj->object->getRefId()));
		
		include_once 'Modules/ItemGroup/classes/class.ilItemGroupItems.php';
		$this->item_group_items = new ilItemGroupItems($a_parent_obj->object->getId());
		$this->items = $this->item_group_items->getItems();
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
		
		$this->getMaterials();
		$this->setTitle($lng->txt("itgr_assigned_materials"));
		
		$this->addColumn("", "", "1px", true);
		$this->addColumn($this->lng->txt("itgr_item"));
		$this->addColumn($this->lng->txt("itgr_assignment"));
		
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.item_group_items_row.html", "Modules/ItemGroup");

		$this->addCommandButton("saveItemAssignment", $lng->txt("save"));
	}
	
	/**
	 * Get materials
	 *
	 * @param
	 * @return
	 */
	function getMaterials()
	{
		$materials = array();
		$nodes = $this->tree->getSubTree(
			$this->tree->getNodeData($this->parent_node["child"]));

		foreach($nodes as $node)
		{
			// filter side blocks and session, item groups and role folder
			if ($node['child'] == $this->parent_node["child"] ||
				$this->obj_def->isSideBlock($node['type']) ||
				in_array($node['type'], array('sess', 'itgr', 'rolf')))
			{
				continue;
			}

			$node["sorthash"] = (int)(!in_array($node['ref_id'], $this->items)).$node["title"];
			$materials[] = $node;
		}
		
		$materials = ilUtil::sortArray($materials, "sorthash", "ASC");
		$this->setData($materials);
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("ITEM_REF_ID", $a_set["child"]);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		
		if (in_array($a_set["child"], $this->items))
		{
			$this->tpl->setVariable("IMG_ASSIGNED", ilUtil::img(
				ilUtil::getImagePath("icon_ok.png")));
			$this->tpl->setVariable("CHECKED", "checked='checked'");
		}
		else
		{
			$this->tpl->setVariable("IMG_ASSIGNED", ilUtil::img(
				ilUtil::getImagePath("icon_not_ok.png")));
		}
	}

}
?>
