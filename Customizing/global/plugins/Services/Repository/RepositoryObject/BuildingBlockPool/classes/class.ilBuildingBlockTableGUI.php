<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/Calendar/classes/class.ilDate.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class ilBuildingBlockTableGUI extends ilTable2GUI {
	public function __construct($a_search_opts,$a_parent_obj, $edit = false, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilAccess;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->parent_obj = $a_parent_obj;
		$this->gAccess = $ilAccess;
		$this->edit = $edit;

		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "showContent"));

		$this->setRowTemplate("tpl.gev_building_block_row.html", "Services/GEV/DecentralTrainings");

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("gev_dec_building_block_content"),"content");
		$this->addColumn($this->lng->txt("gev_dec_building_block_target"), 'target');
		
		$this->addColumn($this->lng->txt("gev_dec_training_training_category"), 'training_categories');
		$this->addColumn($this->lng->txt("gev_dec_training_gdv_topic"), 'gdv_topic');
		$this->addColumn($this->lng->txt("gev_dec_training_dbv_topic"), 'dbv_topic');
		$this->addColumn($this->lng->txt("gev_dec_training_topic"), 'topic');

		$this->addColumn($this->lng->txt("gev_dec_building_block_is_wp_relevant"), "is_wp_relevant");
		$this->addColumn($this->lng->txt("gev_dec_building_block_active"), "is_active");
		$this->addColumn($this->lng->txt("last_change"), "last_change");
		$this->addColumn($this->lng->txt("action"));

		$order = $this->getOrderField();
		$order_direction = $this->getOrderDirection();
		$offset = $this->getOffset();
		$limit = $this->getLimit();

		$data = gevBuildingBlockUtils::getAllBuildingBlocks($a_search_opts,$order,$order_direction,$offset,$limit);

		$this->setMaxCount(gevBuildingBlockUtils::countAllBuildingBlocks($a_search_opts));
		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CONTENT", $a_set["content"]);
		$this->tpl->setVariable("TARGET", $a_set["target"]);

		$this->tpl->setVariable("TRAINING_CAT", implode("<br />",$a_set["training_categories"]));
		$this->tpl->setVariable("GDV_TOPIC", $a_set["gdv_topic"]);
		$this->tpl->setVariable("DBV_TOPIC", $a_set["dbv_topic"]);
		$this->tpl->setVariable("TOPIC", $a_set["topic"]);

		$this->tpl->setVariable("IS_WP_RELEVANT", ($a_set["is_wp_relevant"]) ? "Ja" : "Nein");
		$this->tpl->setVariable("IS_ACTIVE", ($a_set["is_active"]) ? "Ja" : "Nein");
		$date = new ilDate($a_set["last_change_date"], IL_CAL_DATE);
		$this->tpl->setVariable("LAST_CHANGE", $a_set["login"].", ".ilDatePresentation::formatDate($date));
		$this->tpl->setVariable("ACTION", $this->edit ? $this->addActionMenu($a_set) : "");
	}

	protected function addActionMenu($a_set) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(true && false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->lng ->txt("actions"));
		$current_selection_list->setId($a_set["obj_id"]);
		$current_selection_list->setSelectionHeaderClass("small");
		$current_selection_list->setItemLinkClass("xsmall");
		$current_selection_list->setLinksMode("il_ContainerItemCommand2");
		$current_selection_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$current_selection_list->setUseImages(false);
		$current_selection_list->setAdditionalToggleElement("obj_id.".$a_set["obj_id"], "ilContainerListItemOuterHighlight");
		
		$this->addActionMenuItems($current_selection_list, $a_set);

		return $current_selection_list->getHTML();
	}

	protected function addActionMenuItems(&$current_selection_list, $a_set) {
		foreach ($this->getActionMenuItems($a_set) as $key => $value) {
			$current_selection_list->addItem($value["title"],"",$value["link"],$value["image"],"",$value["frame"]);
		}
	}

	protected function getActionMenuItems($a_set) {
		$this->ctrl->setParameter($this->parent_obj, "bb_id", $a_set['obj_id']);
		$edit_link = $this->ctrl->getLinkTarget($this->parent_obj, "editBuildingBlock");
		$delete_link = $this->ctrl->getLinkTarget($this->parent_obj, "deleteBuildingBlock");
		$this->ctrl->clearParameters($this->parent_obj);

		$items = array();

		if($this->gAccess->checkAccess("write", "", $this->parent_obj->object->getRefId())) {
			array_push($items, array("title"=>$this->lng->txt("gev_dec_building_block_edit"),"link"=>$edit_link,"image"=>"","frame"=>""));
			array_push($items, array("title"=>$this->lng->txt("gev_dec_building_block_delete"),"link"=>$delete_link,"image"=>"","frame"=>""));
		}

		return $items;
	}
}

?>