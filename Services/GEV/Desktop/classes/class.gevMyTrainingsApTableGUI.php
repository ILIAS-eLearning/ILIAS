<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses the user is tutoring.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*/

require_once("Services/CaTUIComponents/classes/class.catAccordionTableGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");

require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevGeneralUtils.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php";

require_once("Services/Calendar/classes/class.ilDatePresentation.php");
//require_once("Services/Calendar/classes/class.ilDate.php");

require_once("Services/TEP/classes/class.ilTEPView.php");

class gevMyTrainingsApTableGUI extends catAccordionTableGUI {
	public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilAccess;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gAccess = $ilAccess;

		$user_util = gevUserUtils::getInstance($a_user_id);
		$this->user_id = $a_user_id;

		$this->setEnableTitle(true);
		$this->setTitle("gev_mytrainingsap_title");
		$this->setSubtitle("gev_mytrainingsap_title_desc");
		$this->setImage("GEV_img/ico-head-my-training-deployments.png");
	
		$this->memberlist_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-eye.png").'" />';
		$this->setstatus_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-state-neutral.png").'" />';
		$this->overnight_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-edit.png").'" />';
		$this->bookings_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-booking.png").'" />';
		$this->virtualclass_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-classroom.png").'" />';
		$this->maillog_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-invitation.png").'" />';
		$this->signature_list_img = '<img src="'.ilUtil::getImagePath("GEV_img/icon-table-signature.png").'" />';
		$this->schedule_list_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-flowchart.png").'" />';
		$this->csn_list_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-calllist.png").'" />';
		$this->cancel_training_img = '<img src="'.ilUtil::getImagePath("gev_cancel_action.png").'" />';

		$legend = new catLegendGUI();
		$legend->addItem($this->memberlist_img, "gev_mytrainingsap_legend_memberlist")
			   ->addItem($this->setstatus_img, "gev_mytrainingsap_legend_setstatus")
			   ->addItem($this->overnight_img, "gev_mytrainingsap_legend_overnights")
			   ->addItem($this->bookings_img, "gev_mytrainingsap_legend_view_bookings")
			   ->addItem($this->virtualclass_img, "gev_virtual_class")
			   ->addItem($this->maillog_img, "gev_mail_log")
			   ->addItem($this->signature_list_img, "gev_signature_list")
			   ->addItem($this->schedule_list_img, "gev_dec_crs_building_block_title")
			   ->addItem($this->csn_list_img, "gev_csn_list")
			   ->addItem($this->cancel_training_img, "gev_cancel_training")
			   ;
		$this->setLegend($legend);

		
		$this->setRowTemplate("tpl.gev_my_trainingsap_row.html", "Services/GEV/Desktop");

		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));
		
		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);

		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");

		$this->addColumn($this->gLng->txt("title"), "title");
		$this->addColumn($this->gLng->txt("gev_training_id"), "custom_id");
		$this->addColumn($this->gLng->txt("gev_learning_type"), "type");
		//$this->addColumn($this->gLng->txt("gev_learning_cat"), "category");
		$this->addColumn($this->gLng->txt("gev_location"), "location");
		$this->addColumn($this->gLng->txt("date"), "start_date", "112px");
		$this->addColumn($this->gLng->txt("apdays"));
		$this->addColumn($this->gLng->txt("mbrcount"));
		$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', null, "30px", false);

		$tmp = explode(":", $_GET["_table_nav"]);
		$data = $user_util->getMyAppointmentsCourseInformation($tmp[0], $tmp[1]);

		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$crs_utils = gevCourseUtils::getInstance($a_set["obj_id"]);
		
		$this->tpl->setVariable("ACCORDION_BUTTON_CLASS", $this->getAccordionButtonExpanderClass());
		$this->tpl->setVariable("ACCORDION_ROW", $this->getAccordionRowClass());
		$this->tpl->setVariable("COLSPAN", $this->getColspan());

		if($a_set['target_group']){
			$target_groups_str = '<ul>';
			foreach ($a_set['target_group'] as $tg){
				$target_groups_str .= '<li>' .$tg .'</li>';
			}
			$target_groups_str .= '</ul>';
		}

		if ($a_set["start_date"] == null || $a_set["end_date"] == null) {
			if ($a_set["scheduled_for"] == null) {
				$date = $this->gLng->txt("gev_table_no_entry");
			}
			else {
				$date = $a_set["scheduled_for"];
			}
		}
		else {
			$date = ilDatePresentation::formatPeriod($a_set["start_date"], $a_set["end_date"]);
		}

		//$now = new ilDate(date("Y-m-d"), IL_CAL_DATE);
		//trainer days:
		$apdays_str = gevGeneralUtils::foldConsecutiveDays($a_set['apdays'], "<br />");
		
		$mbrs = $a_set['mbr_booked'] .' (' .$a_set['mbr_waiting'] .')'
				.' / ' .$a_set['mbr_min'] .'-' .$a_set['mbr_max'];

		
		$this->gCtrl->setParameterByClass("gevMemberListDeliveryGUI", "ref_id", $a_set["crs_ref_id"]);
		$memberlist_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "trainer");
		$signature_list_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "download_signature_list");
		$schedule_list_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "download_crs_schedule");
		$csn_list_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "csn");
		$this->gCtrl->clearParametersByClass("gevMemberListDeliveryGUI");
		
		$this->gCtrl->setParameter($this->parent_obj, "crsrefid", $a_set['crs_ref_id']);
		$this->gCtrl->setParameter($this->parent_obj, "crs_id", $a_set['obj_id']);
		$setstatus_link = $this->gCtrl->getLinkTarget($this->parent_obj, "listStatus");
		$overnights_link = $this->gCtrl->getLinkTarget($this->parent_obj, "showOvernights");
		$bookings_link = $this->gCtrl->getLinkTarget($this->parent_obj, "viewBookings");
		$this->gCtrl->clearParameters($this->parent_obj);

		$this->gCtrl->setParameterByClass("ilObjCourseGUI", "ref_id", $a_set["crs_ref_id"]);
		$cancel_training_link = $this->gCtrl->getLinkTargetByClass("ilObjCourseGUI","confirmTrainingCancellation");
		$this->gCtrl->clearParametersByClass("ilObjCourseGUI");

		$view_bookings = $crs_utils->canViewBookings($this->user_id);

		$actions = "";

		if($crs_utils->userHasRightOf($this->user_id, gevSettings::LOAD_MEMBER_LIST)){
			$actions .= '<a href="'.$memberlist_link.'" title="'.$this->gLng->txt("gev_mytrainingsap_legend_memberlist").'">'.$this->memberlist_img.'</a>';
		}

		if($a_set['may_finalize']) {
			$actions .='&nbsp;<a href="'.$setstatus_link.'" title="'.$this->gLng->txt("gev_mytrainingsap_legend_setstatus").'">'.$this->setstatus_img.'</a>';
		}
		
		// is true after training start
		if ($crs_utils->isWithAccomodations() && !$a_set["may_finalize"]) {
			$actions .= '&nbsp;<a href="'.$overnights_link.'" title="'.$this->gLng->txt("gev_mytrainingsap_legend_overnights").'">'.$this->overnight_img.'</a>';
		}
		
		if ($view_bookings) {
			$actions .= '&nbsp;<a href="'.$bookings_link.'" title="'.$this->gLng->txt("gev_mytrainingsap_legend_view_bookings").'">'.$this->bookings_img.'</a>';
		}

		if ($crs_utils->getVirtualClassLink() !== null) {
			$actions .= '&nbsp;<a href="'.$crs_utils->getVirtualClassLink().'" target="_blank" title="'.$this->gLng->txt("gev_virtual_class").'">'.$this->virtualclass_img.'</a>';
		}

		if($crs_utils->userHasRightOf($this->user_id, gevSettings::VIEW_MAILLOG)){
			$this->gCtrl->setParameterByClass("gevMaillogGUI", "obj_id", $a_set["obj_id"]);
			$actions .= '&nbsp;<a href="'.$this->gCtrl->getLinkTargetByClass("gevMaillogGUI", "showMaillog").'" title="'.$this->gLng->txt("gev_mail_log").'">'.$this->maillog_img.'</a>';
			$this->gCtrl->clearParametersByClass("gevMaillogGUI");
		}

		if($crs_utils->userHasRightOf($this->user_id, gevSettings::LOAD_SIGNATURE_LIST)){
			$actions .= '&nbsp;<a href="'.$signature_list_link.'" title="'.$this->gLng->txt("gev_signature_list").'">'.$this->signature_list_img.'</a>';
		}
		

		if($crs_utils->isFlexibleDecentrallTraining() && ($crs_utils->hasTrainer($this->user_id) && $crs_utils->userHasRightOf($this->user_id, gevSettings::VIEW_SCHEDULE_PDF))) {
			$actions .= '&nbsp;<a href="'.$schedule_list_link.'" title="'.$this->gLng->txt("gev_dec_crs_building_block_title").'">'.$this->schedule_list_img.'</a>';
		}

		if($crs_utils->userHasRightOf($this->user_id, gevSettings::LOAD_CSN_LIST) && $crs_utils->getVirtualClassType() == "CSN"){
			$actions .= '&nbsp;<a href="'.$csn_list_link.'" title="'.$this->gLng->txt("gev_csn_list").'">'.$this->csn_list_img.'</a>';
		}

		$now = @date("Y-m-d");
		$start_date = $crs_utils->getStartDate();
		if ($crs_utils->userHasRightOf($this->user_id, gevSettings::CANCEL_TRAINING) && 
			!$crs_utils->getCourse()->getOfflineStatus() && 
			$start_date !== null && 
			($start_date->get(IL_CAL_DATE) > $now || ($start_date->get(IL_CAL_DATE) == $now && !$crs_utils->isFinalized()))) 
		{
			$actions .= '&nbsp;<a href="'.$cancel_training_link.'" title="'.$this->gLng->txt("gev_cancel_training").'">'.$this->cancel_training_img.'</a>';
		}

		$course_link = ilTEPView::getTitleLinkForCourse($this->gAccess, $this->gCtrl, $a_set["crs_ref_id"]);

		$this->tpl->setVariable("TITLE_LINK", $course_link);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CUSTOM_ID", $a_set["custom_id"]);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		//$this->tpl->setVariable("CATEGORY", $a_set["category"]);
		$this->tpl->setVariable("LOCATION", ($a_set["location"] != "") ? $a_set["location"] : $a_set["location_free_text"]);
		$this->tpl->setVariable("DATE", $date);
		
		$this->tpl->setVariable("APDAYS", $apdays_str);

		$this->tpl->setVariable("MBRS", $mbrs);
		$this->tpl->setVariable("ACTIONS", $actions);
		//inner content
		//$this->tpl->setVariable("TARGET_GROUP", $a_set["target_group"]);
		$this->tpl->setVariable("TARGET_GROUP", $target_groups_str);
		$this->tpl->setVariable("TARGET_GROUP_DESC", $a_set["target_group_desc"]);
		$this->tpl->setVariable("GOALS", $a_set["goals"]);
		$this->tpl->setVariable("CONTENTS", $a_set["content"]);
		$this->tpl->setVariable("MBMRLST_LINK", $memberlist_link);
		$this->tpl->setVariable("MBMRLST_LINK_TXT", $this->gLng->txt('gev_mytrainingsap_btn_memberlist'));
		$this->tpl->setVariable("SIGNATURE_LIST_LINK", $signature_list_link);
		$this->tpl->setVariable("SIGNATURE_LIST_LINK_TXT", $this->gLng->txt('gev_signature_list'));
		if ($a_set['may_finalize']) {
			$this->tpl->setCurrentBlock("set_stat");
			$this->tpl->setVariable("SETSTAT_LINK", $setstatus_link);
			$this->tpl->setVariable("SETSTAT_LINK_TXT", $this->gLng->txt('gev_mytrainingsap_btn_setstatus'));
			$this->tpl->parseCurrentBlock();
		}
		if( $view_bookings ) {
			$this->tpl->setCurrentBlock("view_bookings");
			$this->tpl->setVariable("VIEW_BOOKINGS_LINK", $bookings_link);
			$this->tpl->setVariable("VIEW_BOOKINGS_LINK_TXT", $this->gLng->txt('gev_mytrainingsap_btn_bookings'));
			$this->tpl->parseCurrentBlock();
		}

		$actions = "";
		if($crs_utils->isVirtualTraining()) {
			$this->tpl->setVariable("VC_HEADER", "Zugangsdaten virtueller Klassenraum");
		
			if($crs_utils->getVirtualClassLoginTutor()) {
			$actions .= "Login: ".$crs_utils->getVirtualClassLoginTutor()."<br />";
			}

			if($crs_utils->getVirtualClassPasswordTutor()) {
				$actions .= "Passwort: ".$crs_utils->getVirtualClassPasswordTutor();
			}

			$this->tpl->setVariable("VC_DATA", $actions);
		}
		
	}
}

?>