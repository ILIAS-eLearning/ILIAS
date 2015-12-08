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
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once 'Services/Calendar/classes/class.ilDateTime.php';

class gevCoursesTableGUI extends catAccordionTableGUI {
	const CUM_TEMPORE_MIN = 120;
	public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;

		$user_util = gevUserUtils::getInstance($a_user_id);

		$this->user_id = $a_user_id;

		$this->setEnableTitle(true);
		$this->setTitle("gev_my_courses");
		$this->setSubtitle("gev_my_courses_desc");
		$this->setImage("GEV_img/ico-head-my-training-deployments.png");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));

		$data = $user_util->getBookedAndWaitingCourseInformation();

		$cnt = count($data);
		$this->setMaxCount($cnt);
		$this->setLimit($cnt);
		$this->setExternalSegmentation(true);

		$this->setTopCommands(false);
		$this->setEnableHeader(true);

		$this->setRowTemplate("tpl.gev_my_courses_row.html", "Services/GEV/Desktop");
		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");
		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("status"), "status");
		$this->addColumn($this->lng->txt("gev_learning_type"), "type");
		$this->addColumn($this->lng->txt("gev_location"), "location");
		$this->addColumn($this->lng->txt("date"), "start_date");
		$this->addColumn($this->lng->txt("gev_points"), "points");
		$this->addColumn("&euro;", "fee");
		$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', "actions", "20px", false);

		$this->cancel_img = '<img src="'.ilUtil::getImagePath("gev_cancel_action.png").'" />';
		$this->booked_img = '<img src="'.ilUtil::getImagePath("gev_booked_icon.png").'" />';
		$this->waiting_img = '<img src="'.ilUtil::getImagePath("gev_waiting_icon.png").'" />';
		$this->virtualclass_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-classroom.png").'" />';

		$legend = new catLegendGUI();
		$legend->addItem($this->cancel_img, "gev_cancel_training")
			   ->addItem($this->booked_img, "gev_booked")
			   ->addItem($this->waiting_img, "gev_waiting")
			   ->addItem($this->virtualclass_img, "gev_virtual_class");
		$this->setLegend($legend);

		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("ACCORDION_BUTTON_CLASS", $this->getAccordionButtonExpanderClass());
		$this->tpl->setVariable("ACCORDION_ROW", $this->getAccordionRowClass());
		$this->tpl->setVariable("COLSPAN", $this->getColspan());
		$crs_utils = gevCourseUtils::getInstance($a_set["obj_id"]);
		// i know this has timezone issues....
		$now_str = @date("Y-m-d");
		$now = new ilDate($now_str, IL_CAL_DATE);

		if ($a_set["end_date"] === null) {
			$a_set["end_date"] = $a_set["start_date"];
		}

		if ($a_set["start_date"] === null) {
			if ($a_set["scheduled_for"] === null) {
				$date = $this->lng->txt("gev_table_no_entry");
			}
			else {
				$date = $a_set["scheduled_for"];
			}
		}
		else {
			$date = ilDatePresentation::formatPeriod($a_set["start_date"], $a_set["end_date"]);
		}

		if ($a_set["status"] == ilCourseBooking::STATUS_BOOKED) {
			$status = $this->booked_img;
		}
		else if($a_set["status"] == ilCourseBooking::STATUS_WAITING) {
			$status = $this->waiting_img;
		}
		else {
			$status = "";
		}


		$action = "";
		$show_cancel_link = 
			(  $a_set["start_date"] === null 
			|| (   ilDateTime::_before($now, $a_set["start_date"]   )
				&& (   $a_set["absolute_cancel_date"] === null
					|| !ilDateTime::_before($a_set["absolute_cancel_date"], $now)
				   )
			   )
			)
			&& $a_set["type"] != "Selbstlernkurs"
			;

		if ($show_cancel_link) {
			$action .= '<a href="'.gevCourseUtils::getCancelLinkTo($a_set["obj_id"], $this->user_id).'">'.
					  $this->cancel_img."</a>";
		}
		
		$datetime = new ilDateTime();
		$offset =  $datetime->getUTCOffset(); 

		if ( $a_set["start_date"] && $a_set["end_date"] && $a_set["crs_amd_schedule"] ) {
			$show_webex_link = gevCourseUtils::timeWithinCourse(
				time() - $offset , self::CUM_TEMPORE_MIN, $a_set["start_date"], $a_set["end_date"], $a_set["crs_amd_schedule"]
			) && ($crs_utils->getVirtualClassLink()!==null);
		}

		if ($show_webex_link ) {
			$action .= '&nbsp;<a href="'.$crs_utils->getVirtualClassLink().'" target="_blank">'.$this->virtualclass_img.'</a>';
		}

		$action = ltrim($action,"&nbsp;");

		$show_cancel_date = true;
		if ($a_set["cancel_date"] == null) {
			$cancel_date = $this->lng->txt("gev_unlimited");
		}
		else {
			$cancel_date = ilDatePresentation::formatDate($a_set["cancel_date"]);
			$show_cancel_date = ilDateTime::_before($now, $a_set["cancel_date"]);;
		}
		
		$show_absolute_cancel_date = true;
		if ($a_set["absolute_cancel_date"] == null) {
			$absolute_cancel_date = $this->lng->txt("gev_unlimited");
		}
		else {
			$absolute_cancel_date = ilDatePresentation::formatDate($a_set["absolute_cancel_date"]);
			$show_absolute_cancel_date = ilDateTime::_before($now, $a_set["absolute_cancel_date"]);;
		}


		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("STATUS", $status);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		$this->tpl->setVariable("LOCATION", ($a_set["location"] != "") ? $a_set["location"] : $a_set["location_free_text"]);
		$this->tpl->setVariable("DATE", $date);
		$this->tpl->setVariable("POINTS", $a_set["points"]);
		$this->tpl->setVariable("FEE", gevCourseUtils::formatFee($a_set["fee"]));
		$this->tpl->setVariable("ACTIONS", $action);
		$this->tpl->setVariable("TARGET_GROUP", $a_set["target_group"]);
		$this->tpl->setVariable("GOALS", $a_set["goals"]);
		$this->tpl->setVariable("CONTENTS", $a_set["content"]);
		$this->tpl->setVariable("CRS_LINK", gevCourseUtils::getLinkTo($a_set["obj_id"]));

		
		$tutors = $crs_utils->getTrainers(true);
		$tutors = implode("; ", $tutors);

		$this->tpl->setVariable("TUTORS", $tutors);

		if ($a_set["overnights"]) {
			$this->tpl->setCurrentBlock("overnights");
			$this->tpl->setVariable("OVERNIGHTS", $a_set["overnights"]);
			$this->tpl->parseCurrentBlock();
		}
		if ($show_cancel_link && $show_cancel_date) {
			$this->tpl->setCurrentBlock("cancel_date");
			$this->tpl->setVariable("CANCEL_DATE", $cancel_date);
			$this->tpl->parseCurrentBlock();
		}
		if ($show_cancel_link && $show_absolute_cancel_date) {
			$this->tpl->setCurrentBlock("absolute_cancel_date");
			$this->tpl->setVariable("ABSOLUTE_CANCEL_DATE", $absolute_cancel_date);
			$this->tpl->parseCurrentBlock();
		}

	}
	
	// overwritten from ilTable2GUI to get sorting of fee right.
	function numericOrdering($a_field)
	{
		return $a_field == "fee";
	}
}

?>