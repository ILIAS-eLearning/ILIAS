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
require_once("Services/Calendar/classes/class.ilDatePresentation.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/CourseBooking/classes/class.ilCourseBookingHelper.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");

class ilAdminSearchTableGUI extends catAccordionTableGUI {
	public function __construct($a_search_options, $a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		
		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.il_admin_search_row.html", "Services/GEV/Desktop");

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("status"));
		$this->addColumn($this->lng->txt("gev_course_id"), 'custom_id');
		$this->addColumn($this->lng->txt("gev_learning_type"), "type");
		$this->addColumn($this->lng->txt("gev_location"), "location");
		$this->addColumn($this->lng->txt("date"), "date");
		$this->addColumn($this->lng->txt("tutor"));
		$this->addColumn($this->lng->txt("gev_points"), "points");
		$this->addColumn("&euro;", "fee");
		$this->addColumn($this->lng->txt("mbrcount"));
		$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', null, "20px", false);
	
		//legend
		$this->memberlist_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-eye.png").'" />';
		$legend = new catLegendGUI();
		$legend->addItem($this->memberlist_img, "gev_mytrainingsap_legend_memberlist");
		$this->setLegend($legend);

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
			$date = $this->lng->txt("gev_table_no_entry");
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
		$this->tpl->setVariable("ACTIONS", $a_set["action"]);
	}
}

?>