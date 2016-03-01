<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses of a user for Generali.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catAccordionTableGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/CourseBooking/classes/class.ilCourseBookingHelper.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");

class ilAdminSearchTableGUI extends catAccordionTableGUI {
	public function __construct($a_search_options, $a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilUser;

		$this->gLng = &$lng;
		$this->gCtrl = &$ilCtrl;
		$this->gUser = $ilUser;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($this->gCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.il_admin_search_row.html", "Services/GEV/Desktop");

		$this->addColumn($this->gLng->txt("title"), "title");
		$this->addColumn($this->gLng->txt("status"));
		$this->addColumn($this->gLng->txt("gev_course_id"), 'custom_id');
		$this->addColumn($this->gLng->txt("gev_learning_type"), "type");
		$this->addColumn($this->gLng->txt("gev_location"), "location");
		$this->addColumn($this->gLng->txt("date"), "date");
		$this->addColumn($this->gLng->txt("tutor"));
		$this->addColumn($this->gLng->txt("gev_points"), "points");
		$this->addColumn("&euro;", "fee");
		$this->addColumn($this->gLng->txt("mbrcount"));
		$this->addColumn($this->gLng->txt("actions"), null, "20px", false);
	
		//legend
		$this->memberlist_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-eye.png").'" />';

		$order = $this->getOrderField();
		
		//                      #671
		if ($order == "date" || $order == "") {
			$order = "start_date";
		}


		$data = gevCourseUtils::searchCourses(
			$a_search_options, 
			$this->getOffset(),
			$this->getLimit(),
			$order,
			$this->getOrderDirection()
		);

		$this->setMaxCount($data["count"]);
		$this->setData($data["info"]);
	}

	protected function fillRow($a_set) {
		if ($a_set["end_date"] === null) {
			$a_set["end_date"] = $a_set["start_date"];
		}

		if ($a_set["start_date"] == null) {
			$date = $this->gLng->txt("gev_table_no_entry");
		}
		else {
			$date = ilDatePresentation::formatPeriod($a_set["start_date"], $a_set["end_date"]);
		}

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("STATUS", $a_set["status"]);
		$this->tpl->setVariable("CUSTOM_ID", $a_set["custom_id"]);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		$this->tpl->setVariable("LOCATION", $a_set["location"]?$a_set["location"]:"-");
		$this->tpl->setVariable("DATE", $date);
		$this->tpl->setVariable("TRAINER", $a_set['trainer']);
		$this->tpl->setVariable("POINTS", $a_set["points"]);
		$this->tpl->setVariable("FEE", gevCourseUtils::formatFee($a_set["fee"]));
		$this->tpl->setVariable("MEMBERS", $a_set["members"]);
		$this->tpl->setVariable("ACTIONS", $this->addActionMenu($a_set));
	}

	protected function addActionMenu($a_set) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->gLng->txt("actions"));
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

	protected function addActionMenuItems($current_selection_list, $a_set) {
		foreach ($this->getActionMenuItems($a_set) as $key => $value) {
			$current_selection_list->addItem($value["title"],"",$value["link"],$value["image"],"",$value["frame"]);
		}
	}

	protected function getActionMenuItems($a_set) {
		//Prepare links
		$this->gCtrl->setParameterByClass("gevMemberListDeliveryGUI", "ref_id", gevObjectUtils::getRefId($a_set["obj_id"]));
		$memberlist_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "trainer");
		$this->gCtrl->clearParametersByClass("gevMemberListDeliveryGUI");

		//prepare crs utils
		$crs_utils = gevCourseUtils::getInstance($a_set["obj_id"]);

		$items = array();
		if($crs_utils->userHasPermissionTo($this->gUser->getId(), gevSettings::LOAD_MEMBER_LIST)){
			$items[] = array("title" => $this->gLng->txt("gev_mytrainingsap_legend_memberlist"), "link" => $memberlist_link, "image" => $this->memberlist_img, "frame"=>"");
		}

		return $items;
	}
}