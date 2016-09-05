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
require_once("Services/GEV/CourseSearch/classes/class.gevCourseSearch.php");

class gevCourseSearchTableGUI extends catAccordionTableGUI {
	public function __construct($a_search_options, $a_user_id, $a_parent_obj, $a_active_tab, $a_in_search = false, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilUser;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;

		$this->active_tab = $a_active_tab;
		$this->in_search = $a_in_search;
		$this->parent_obj = $a_parent_obj;
		$this->search_options = $a_search_options;

		//$crs_srch = gevUserUtils::getInstance($a_user_id);
		$crs_srch = gevCourseSearch::getInstance($a_user_id);
		$this->user_id = $a_user_id;
		$this->is_logged_in = $ilUser->getId() != 0;

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$cnt = count($crs_srch->getPotentiallyBookableCourseIds($a_search_options));
		$this->setMaxCount($cnt);
		$this->determineOffsetAndOrder();
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$this->setRowTemplate("tpl.gev_course_search_row.html", "Services/GEV/CourseSearch");

		//$this->addColumn("", "expand", "20px");
		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");
		$this->addColumn($this->gLng->txt("title"), "title");
		$this->addColumn($this->gLng->txt("status"));
		$this->addColumn($this->gLng->txt("gev_learning_type"), "type");
		$this->addColumn($this->gLng->txt("gev_location"), "location");
		$this->addColumn($this->gLng->txt("date"), "date");
		$this->addColumn($this->gLng->txt("gev_points"), "points");
		$this->addColumn("&euro;", "fee");
		//$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', "", "20px");
		$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', null, "20px", false);
		
		$this->book_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-booking.png").'" />';
		$this->email_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-invitation.png").'" />';
		$this->bookable_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		$this->bookable_waiting_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		$this->almost_not_bookable_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-violet.png").'" />';
		$this->not_bookable_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';

		$legend = new catLegendGUI();
		$legend->addItem($this->book_img, "gev_book_course")
			   ->addItem($this->email_img, "gev_book_contact_pe")
			   ->addItem($this->bookable_img, "gev_bookable")
			   ->addItem($this->bookable_waiting_img, "gev_bookable_waiting")
			   ->addItem($this->not_bookable_img, "gev_not_bookable")
			   ->addItem($this->almost_not_bookable_img, "gev_booking_request_pe");
		$this->setLegend($legend);


		$order = $this->getOrderField();
		
		if ($order == "status") {
			// TODO: This will not make the user happy.
			$order = "title";
		}
		//                      #671
		// new course search
		if(!$crs_srch->isActiveTabSelflearning($this->active_tab)){
			if ($order == "date" || $order == "") {
				$order = "start_date";
			}
		} else {
			$order = "title";
		}
		

		$this->setData($crs_srch->getPotentiallyBookableCourseInformation(
										$a_search_options, 
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

		if ($a_set["end_date"] === null) {
			$a_set["end_date"] = $a_set["start_date"];
		}

		if ($a_set["start_date"] == null) {
			$date = $this->gLng->txt("gev_table_no_entry");
		}
		else {
			$date = ilDatePresentation::formatPeriod($a_set["start_date"], $a_set["end_date"]);
		}

		$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		
		$unlimited = $a_set["max_participants"] == 0;

		$booking_deadline_expired = $a_set["booking_date"] ? (date("Y-m-d") > $a_set["booking_date"]->get(IL_CAL_DATE)):false;
		$bookable = !$booking_deadline_expired && ($a_set["free_places"] > 0 || $a_set["waiting_list_active"]);
		
		$target = $this->is_logged_in ? "" : 'target="_blank"';
		$booking_action = '<a href="'.gevCourseUtils::getBookingLinkTo($a_set["obj_id"], $this->user_id).'" '.$target.'>'.
						  $this->book_img."</a>";
		$contact_onside_action = '<a href="mailto:'.$this->gLng->txt("gev_book_contact_onside").'">'.$this->email_img.'</a>';
		$contact_webinar_action = '<a href="mailto:'.$this->gLng->txt("gev_book_contact_webinar").'">'.$this->email_img.'</a>';

		if (!$booking_deadline_expired && ($a_set["free_places"] > 0 || $unlimited)) {
			$status = $this->bookable_img;
			$action = $booking_action;
		}
		else if(!$booking_deadline_expired && $a_set["free_places"] == 0 && $a_set["waiting_list_active"] && !$a_set["waiting_list_full"]) {
			$status = $this->bookable_waiting_img;
			$action = $booking_action;
		}
		else if ($a_set["free_places"] == 0 && !$a_set["waiting_list_active"]) {
			$status = $this->not_bookable_img;
			if ($a_set["type"] == "Webinar") {
				$action = $contact_onside_action;
			}
			else if(preg_match("/^Pr..senztraining$/", $a_set["type"])) {
				$action = $contact_webinar_action;
			}
		}
		else {
			$status = $this->almost_not_bookable_img;
			if ($a_set["type"] == "Webinar") {
				$action = $contact_webinar_action;
			}
			else if(preg_match("/^Pr.*senztraining$/", $a_set["type"])) {
				$action = $contact_onside_action;	
			}
		}

		//storno?
		//$a_set["start_date"]
		//-$a_set["cancel_date"]
		$show_cancel_date = false;
		if($a_set["cancel_date"]) {
			$show_cancel_date = ilDateTime::_before($now, $a_set["cancel_date"]);;
		}


		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("STATUS", $status);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		$this->tpl->setVariable("LOCATION", $a_set["location"]);
		$this->tpl->setVariable("DATE", $date);
		$this->tpl->setVariable("POINTS", $a_set["points"]);
		$this->tpl->setVariable("FEE", gevCourseUtils::formatFee($a_set["fee"]));
		$this->tpl->setVariable("ACTIONS", $action);
		$this->tpl->setVariable("TARGET_GROUP", $a_set["target_group"]);
		$this->tpl->setVariable("GOALS", $a_set["goals"]);
		$this->tpl->setVariable("CONTENTS", $a_set["content"]);
		if ($bookable && !$booking_deadline_expired) {
			$this->tpl->setCurrentBlock("booking_deadline");
			$this->tpl->setVariable("BOOKING_LINK", gevCourseUtils::getBookingLinkTo($a_set["obj_id"], $this->user_id));
			$this->tpl->setVariable("TARGET", $target);
			$this->tpl->parseCurrentBlock();
		}
		else if ($status == $this->almost_not_bookable_img) {
			$this->tpl->setCurrentBlock("pe_note");
			$this->tpl->setVariable("PE_NOTE", $this->gLng->txt("gev_booking_request_pe_note"));
			$this->tpl->parseCurrentBlock();
		}

		$crs_utils = gevCourseUtils::getInstance($a_set["obj_id"]);
		$tutors = $crs_utils->getTrainers(true);
		$tutors = implode("; ", $tutors);

		$this->tpl->setVariable("TUTORS", $tutors);

		$this->tpl->setVariable("FREE_PLACES", $unlimited
											 ? $this->gLng->txt("gev_unlimited")
											 : $a_set["free_places"]
											 );
		if ($a_set["booking_date"] !== null) {
			$this->tpl->setCurrentBlock("booking_deadline");
			$this->tpl->setVariable("BOOKING_DEADLINE", ilDatePresentation::formatDate($a_set["booking_date"]));
			$this->tpl->parseCurrentBlock();
		}		
		if ($a_set["cancel_date"] !== null && $show_cancel_date) {
			$this->tpl->setCurrentBlock("cancel_deadline");
			$this->tpl->setVariable("CANCEL_DEADLINE", ilDatePresentation::formatDate($a_set["cancel_date"]));
			//$this->tpl->setVariable("CANCEL_DEADLINE", $a_set["cancel_date"]);
			$this->tpl->parseCurrentBlock();
		}

		if ($a_set["type"] == "Webinar") {
			$this->tpl->setCurrentBlock("webinar_time");
			$this->tpl->setVariable("TIME", $a_set["schedule"][0]);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	
	protected $advice = null;

	public function setAdvice($a_advice) {
		$this->advice = $a_advice;
	}

	public function getAdvice() {
		return $this->advice;
	}

	private function renderAdvice() {
		$tpl = new ilTemplate("tpl.gev_my_advice.html", true, true, "Services/GEV/Desktop");

		$tpl->setCurrentBlock("advice");
		$tpl->setVariable("ADVICE", $this->gLng->txt($this->getAdvice()));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	public function render() {
		$ret = "";
		if($this->in_search) {
			$this->gCtrl->setParameter($this->parent_obj,"cmd", "search");
		}

		if ($this->_title_enabled) {
			$ret .= $this->_title->render()."<br />";
		}
		if($this->advice) {
			$ret .= $this->renderAdvice()."<br />";
		}
		
		$this->gCtrl->setParameter($this->parent_obj,"active_tab",$this->active_tab);
		$ret .= ilTable2GUI::render();
		$this->gCtrl->clearParameters($this->parent_obj);

		return $ret;
	}
}