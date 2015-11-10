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

class gevDecentralTrainingBuildingBlockAdminTableGUI extends catTableGUI {
	public function __construct($a_search_opts,$a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;

		$this->delete_image = '<img src="'.ilUtil::getImagePath("gev_cancel_action.png").'" />';
		$this->edit_image = '<img src="'.ilUtil::getImagePath("GEV_img/ico-edit.png").'" />';

		$this->setEnableTitle(true);
		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.gev_building_block_row.html", "Services/GEV/DecentralTrainings");

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("gev_dec_building_block_content"),"content");
		$this->addColumn($this->lng->txt("gev_dec_building_block_learn_dest"), 'target');
		
		$this->addColumn($this->lng->txt("gev_dec_training_training_category"), 'target');
		$this->addColumn($this->lng->txt("gev_dec_training_gdv_topic"), 'target');
		$this->addColumn($this->lng->txt("gev_dec_training_dbv_topic"), 'target');
		$this->addColumn($this->lng->txt("gev_dec_training_topic"), 'target');

		$this->addColumn($this->lng->txt("gev_dec_building_block_is_wp_relevant"), "is_wp_relevant");
		$this->addColumn($this->lng->txt("gev_dec_building_block_active"), "is_active");
		$this->addColumn($this->lng->txt("last_change"), "last_change");
		$this->addColumn($this->lng->txt("action"), "");

		$legend = new catLegendGUI();
		$legend->addItem($this->edit_image, "gev_dec_building_block_edit")
			   ->addItem($this->delete_image, "gev_dec_building_block_delete");
		$this->setLegend($legend);
		$order = $this->getOrderField();
		$order_direction = $this->getOrderDirection();

		$data = gevBuildingBlockUtils::getAllBuildingBlocks($a_search_opts,$order,$order_direction);

		$this->setMaxCount(count($data));
		$this->setData($data);

		//$this->setTitleTemplate("tpl.cat_title_without_search.html");
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

		$action = '<a href="'.gevBuildingBlockUtils::getEditLink($a_set["obj_id"]).'">'.$this->edit_image.'</a>';
		$action .= '<a href="'.gevBuildingBlockUtils::getDeleteLink($a_set["obj_id"]).'">'.$this->delete_image.'</a>&nbsp;';
		$this->tpl->setVariable("ACTION", $action);
	}
}

?>