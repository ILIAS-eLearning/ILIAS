<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catAccordionTableGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/CourseBooking/classes/class.ilCourseBookingHelper.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");

class gevDecentralTrainingBuildingBlockAdminTableGUI extends catAccordionTableGUI {
	public function __construct($a_search_opts,$a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;

		$this->delete_image = '<img src="'.ilUtil::getImagePath("gev_cancel_action.png").'" />';
		$this->edit_image = '<img src="'.ilUtil::getImagePath("GEV_img/ico-edit.png").'" />';

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.gev_building_block_search_row.html", "Services/GEV/DecentralTrainings");

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("gev_dec_building_block_content"),"content");
		$this->addColumn($this->lng->txt("gev_dec_building_block_learn_dest"), 'learning_dest');
		$this->addColumn($this->lng->txt("gev_dec_building_block_is_wp_relevant"), "is_wp_relevant");
		$this->addColumn($this->lng->txt("gev_dec_building_block_active"), "is_active");
		$this->addColumn($this->lng->txt("action"), "");

		$legend = new catLegendGUI();
		$legend->addItem($this->delete_image, "gev_dec_building_block_delete")
			   ->addItem($this->edit_image, "gev_dec_building_block_edit");
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
		$this->tpl->setVariable("LEARNING_DEST", $a_set["learning_dest"]);
		$this->tpl->setVariable("IS_WP_RELEVANT", ($a_set["is_wp_relevant"]) ? "Ja" : "Nein");
		$this->tpl->setVariable("IS_ACTIVE", ($a_set["is_active"]) ? "Ja" : "Nein");

		$action = '<a href="'.gevBuildingBlockUtils::getDeleteLink($a_set["obj_id"]).'">'.$this->delete_image.'</a>&nbsp;';
		$action .= '<a href="'.gevBuildingBlockUtils::getEditLink($a_set["obj_id"]).'">'.$this->edit_image.'</a>';
		$this->tpl->setVariable("ACTION", $action);
	}
}

?>