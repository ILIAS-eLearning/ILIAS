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

class gevCourseSearchTableGUI extends catAccordionTableGUI {
	public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;

		$user_util = gevUserUtils::getInstance($a_user_id);
		$this->user_id = $a_user_id;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setMaxCount(count($user_util->getPotentiallyBookableCourseIds()));
		$this->determineOffsetAndOrder();

		$this->setRowTemplate("tpl.gev_course_search_row.html", "Services/GEV/Desktop");

		//$this->addColumn("", "expand", "20px");
		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("status"));
		$this->addColumn($this->lng->txt("gev_learning_type"), "type");
		$this->addColumn($this->lng->txt("gev_location"), "location");
		$this->addColumn($this->lng->txt("date"), "date");
		$this->addColumn($this->lng->txt("gev_points"), "points");
		$this->addColumn("&euro;", "fee");
		//$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', "", "20px");
		$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', "actions", "20px", false);


		$this->book_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-booking.png").'" />';
		$this->bookable_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		$this->bookable_waiting_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		$this->not_bookable_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';

		$legend = new catLegendGUI();
		$legend->addItem($this->book_img, "gev_book_course")
			   ->addItem($this->bookable_img, "gev_bookable")
			   ->addItem($this->bookable_waiting_img, "gev_bookable_waiting")
			   ->addItem($this->not_bookable_img, "gev_not_bookable");
		$this->setLegend($legend);

		$order = $this->getOrderField();
		if ($order == "status") {
			// TODO: This will not make the user happy.
			$order = "title";
		}
		if ($order == "date") {
			$order = $start_date;
		}


		$this->setData($user_util->getPotentiallyBookableCourseInformation(
										$this->getOffset(),
										$this->getLimit(),
										$order,
										$this->getOrderDirection()
					   ));
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("ACCORDION_BUTTON_CLASS", $this->getAccordionButtonExpanderClass());
		$this->tpl->setVariable("ACCORDION_ROW", $this->getAccordionRowClass());
		$this->tpl->setVariable("COLSPAN", $this->getColspan());

		if ($a_set["start_date"] == null ) {
			$date = $this->lng->txt("gev_table_no_entry");
		}
		else {
			$date = ilDatePresentation::formatPeriod($a_set["start_date"], $a_set["end_date"]);
		}

		if ($a_set["bookable"]) {
			if ($a_set["free_places"] > 0 || $a_set["free_places"] === null) {
				$status = $this->bookable_img;
			}
			else {
				$status = $this->bookable_waiting_img;
			}
		}
		else {
			$status = $this->not_bookable_img;
		}

		$action = '<a href="'.gevCourseUtils::getBookingLinkTo($a_set["obj_id"], $this->user_id).'">'.
				  $this->book_img."</a>";

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("STATUS", $status);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		$this->tpl->setVariable("LOCATION", $a_set["location"]);
		$this->tpl->setVariable("DATE", $date);
		$this->tpl->setVariable("POINTS", $a_set["points"]);
		$this->tpl->setVariable("FEE", $a_set["fee"]);
		$this->tpl->setVariable("ACTIONS", $action);
		$this->tpl->setVariable("TARGET_GROUP", $a_set["target_group"]);
		$this->tpl->setVariable("GOALS", $a_set["goals"]);
		$this->tpl->setVariable("CONTENTS", $a_set["contents"]);
		if ($a_set["bookable"]) {
			$this->tpl->setCurrentBlock("booking_deadline");
			$this->tpl->setVariable("BOOKING_LINK", gevCourseUtils::getBookingLinkTo($a_set["obj_id"], $this->user_id));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("FREE_PLACES", $a_set["free_places"] === null
											 ? $this->lng->txt("gev_unlimited")
											 : $a_set["free_places"]
											 );
		if ($a_set["booking_date"] !== null) {
			$this->tpl->setCurrentBlock("booking_deadline");
			$this->tpl->setVariable("BOOKING_DEADLINE", $a_set["booking_date"]);
			$this->tpl->parseCurrentBlock();
		}
	}
}

?>