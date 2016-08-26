<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses the user is training creator or manager.
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
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
require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatus.php";
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php";

require_once("Services/Calendar/classes/class.ilDatePresentation.php");
//require_once("Services/Calendar/classes/class.ilDate.php");

require_once("Services/TEP/classes/class.ilTEPView.php");

class gevMyTrainingsAdminTableGUI extends catAccordionTableGUI {
	public function __construct($a_user_id, $a_parent_obj, catFilterFlatViewGUI $filter_form, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng, $ilAccess;

		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gAccess = $ilAccess;

		$user_util = gevUserUtils::getInstance($a_user_id);
		$this->user_id = $a_user_id;
		$this->id = 0;
		$this->filter_form = $filter_form;
		$this->parent = $a_parent_obj;

		$this->setEnableTitle(true);
		$this->setTitle("gev_my_trainings_admin_title");
		$this->setSubtitle("gev_my_trainings_admin_title_desc");
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

		$this->closed_img = '<img src="'.ilUtil::getImagePath("scorm/completed.png").'" />';
		$this->wip_img = '<img src="'.ilUtil::getImagePath("scorm/incomplete.png").'" />';
		$this->not_closed_img = '<img src="'.ilUtil::getImagePath("scorm/failed.png").'" />';

		$this->setRowTemplate("tpl.gev_my_trainingsadmin_row.html", "Services/GEV/Desktop");

		$legend = new catLegendGUI();
		$legend->addItem($this->closed_img, "gev_training_admin_search_closed")
			   ->addItem($this->wip_img, "gev_training_admin_search_wip")
			   ->addItem($this->not_closed_img, "gev_training_admin_search_not_closed");
		$this->setLegend($legend);

		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);

		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");

		$this->addColumn($this->gLng->txt("title"), "title");
		$this->addColumn($this->gLng->txt("gev_training_id"), "custom_id");
		$this->addColumn($this->gLng->txt("gev_learning_type"), "type");
		$this->addColumn($this->gLng->txt("gev_location"), "location");
		$this->addColumn($this->gLng->txt("date"), "start_date", "112px");
		$this->addColumn($this->gLng->txt("tutor"));
		$this->addColumn($this->gLng->txt("gev_my_trainings_admin_credit_points"));
		$this->addColumn($this->gLng->txt("mbrcount"));
		$this->addColumn($this->gLng->txt("status"));
		$this->addColumn($this->gLng->txt("action"));

		$this->setDefaultOrderField("start_date");
		$this->setDefaultOrderDirection("asc");
		$this->determineOffsetAndOrder();

		$data = $this->parent->helper()->getMyTrainingsAdminCourseInformation($this->getOrderField(), $this->getOrderDirection(), gevSettings::$CRS_MANAGER_ROLES);

		$this->setData($data);
	}

	public function render() {
		if ($this->_title_enabled) {
			$html = $this->_title->render()."<br />";
		}

		//TODO: Remove this breaklines!
		return $html.$this->filter_form->render($this->parent->helper()->loadPost())."<br /><br />".ilTable2GUI::render();
	}

	protected function fillRow($a_set) {
		$this->createLinks($a_set["obj_id"], $a_set["crs_ref_id"]);

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
		$mbrs = $a_set['mbr_booked'] .' (' .$a_set['mbr_waiting'] .')'
				.' / ' .$a_set['mbr_min'] .'-' .$a_set['mbr_max'];

		$course_link = $this->getCourseLink($a_set["obj_id"], $a_set["crs_ref_id"]);

		$this->tpl->setVariable("TITLE_LINK", $course_link);
		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CUSTOM_ID", $a_set["custom_id"]);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		//$this->tpl->setVariable("CATEGORY", $a_set["category"]);
		$this->tpl->setVariable("LOCATION", ($a_set["location"] != "") ? $a_set["location"] : $a_set["location_free_text"]);
		$this->tpl->setVariable("DATE", $date);
		$this->tpl->setVariable("TUTOR", implode(", ", $a_set["tutor"]));
		$this->tpl->setVariable("CREDIT_POINTS", $a_set["credit_points"]);
		$this->tpl->setVariable("MBRS", $mbrs);
		$this->tpl->setVariable("STATUS", $this->statusPicture($a_set["status"]));
		$this->tpl->setVariable("ACTIONS", $this->addActionMenu($a_set));

		//inner content
		//$this->tpl->setVariable("TARGET_GROUP", $a_set["target_group"]);
		$this->tpl->setVariable("TARGET_GROUP", $this->target_groups_str);
		$this->tpl->setVariable("TARGET_GROUP_DESC", $a_set["target_group_desc"]);
		$this->tpl->setVariable("GOALS", $a_set["goals"]);
		$this->tpl->setVariable("CONTENTS", $a_set["content"]);
		$this->tpl->setVariable("MBMRLST_LINK", $this->memberlist_link);
		$this->tpl->setVariable("MBMRLST_LINK_TXT", $this->gLng->txt('gev_mytrainingsap_btn_memberlist'));
		$this->tpl->setVariable("SIGNATURE_LIST_LINK", $this->signature_list_link);
		$this->tpl->setVariable("SIGNATURE_LIST_LINK_TXT", $this->gLng->txt('gev_signature_list'));

		if($crs_utils->isVirtualTraining()) {
			$actions = "";
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

	protected function addActionMenu($a_set) {
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setAsynch(false);
		$current_selection_list->setAsynchUrl(true);
		$current_selection_list->setListTitle($this->gLng ->txt("actions"));
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
		//prepare crs utils
		$crs_utils = gevCourseUtils::getInstance($a_set["obj_id"]);

		$items = array();
		if($crs_utils->userHasPermissionTo($this->user_id, gevSettings::LOAD_MEMBER_LIST)){
			$items[] = array("title" => $this->gLng->txt("gev_mytrainingsap_legend_memberlist"), "link" => $this->memberlist_link, "image" => $this->memberlist_img, "frame"=>"");
		}

		if($a_set['may_finalize']) {
			$items[] = array("title" => $this->gLng->txt("gev_mytrainingsap_legend_setstatus"), "link" => $this->setstatus_link, "image" => $this->setstatus_img, "frame"=>"");
		}

		// is true after training start
		if ($crs_utils->isWithAccomodations() && !$a_set["may_finalize"]) {
			$items[] = array("title" => $this->gLng->txt("gev_mytrainingsap_legend_overnights"), "link" => $this->overnights_link, "image" => $this->overnight_img, "frame"=>"");
		}

		if ($crs_utils->canViewBookings($this->user_id)) {
			$items[] = array("title" => $this->gLng->txt("gev_mytrainingsap_legend_view_bookings"), "link" => $this->bookings_link, "image" => $this->bookings_img, "frame"=>"");
		}

		if ($crs_utils->getVirtualClassLink() !== null) {
			$items[] = array("title" => $this->gLng->txt("gev_virtual_class"), "link" => $crs_utils->getVirtualClassLink(), "image" => $this->virtualclass_img, "frame"=>"_blank");
		}

		if($crs_utils->userHasPermissionTo($this->user_id, gevSettings::VIEW_MAILING)){
			$items[] = array("title" => $this->gLng->txt("gev_trainer_view_mailing"), "link" => $this->maillog, "image" => $this->maillog_img, "frame"=>"");
		}

		if($crs_utils->userHasPermissionTo($this->user_id, gevSettings::LOAD_SIGNATURE_LIST)){
			$items[] = array("title" => $this->gLng->txt("gev_signature_list"), "link" => $this->signature_list_link, "image" => $this->signature_list_img, "frame"=>"");
		}

		if($crs_utils->isFlexibleDecentrallTraining() && ($crs_utils->hasTrainer($this->user_id) && $crs_utils->userHasPermissionTo($this->user_id, gevSettings::VIEW_SCHEDULE_PDF))) {
			$items[] = array("title" => $this->gLng->txt("gev_dec_crs_building_block_title"), "link" => $this->schedule_list_link, "image" => $this->schedule_list_img, "frame"=>"");
		}

		if($crs_utils->userHasPermissionTo($this->user_id, gevSettings::LOAD_CSN_LIST) && $crs_utils->getVirtualClassType() == "CSN"){
			$items[] = array("title" => $this->gLng->txt("gev_csn_list"), "link" => $cthis->sn_list_link, "image" => $this->csn_list_img, "frame"=>"");
		}

		if($crs_utils->userHasPermissionTo($this->user_id, "write")){
			$items[] = array("title" => $this->gLng->txt("gev_my_trainings_admin_edit_settings"), "link" => $this->edit_settings_link, "image" => "", "frame"=>"");
		}

		if ($crs_utils->userCanCancelCourse($this->user_id)){
			$items[] = array("title" => $this->gLng->txt("gev_cancel_training"), "link" => $this->cancel_training_link, "image" => $this->cancel_training_img, "frame"=>"");
		}

		if ($crs_utils->userHasPermissionTo($this->user_id, gevSettings::LOAD_SIGNATURE_LIST) && ilParticipationStatus::getInstance($crs_utils->getCourse())->getAttendanceList()) {
			$items[] = array("title" => $this->gLng->txt("gev_attendance_list"), "link" => $this->get_attendance_list_link, "image" => $this->cancel_training_img, "frame"=>"");
		}
		return $items;
	}

	protected function createLinks($crs_obj_id, $crs_ref_id) {
		$this->gCtrl->setParameterByClass("gevMemberListDeliveryGUI", "ref_id", $crs_ref_id);
		$this->memberlist_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "trainer");
		$this->signature_list_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "download_signature_list");
		$this->schedule_list_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "download_crs_schedule");
		$this->csn_list_link = $this->gCtrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "csn");
		$this->gCtrl->clearParametersByClass("gevMemberListDeliveryGUI");

		$this->gCtrl->setParameter($this->parent_obj, "crsrefid", $crs_ref_id);
		$this->gCtrl->setParameter($this->parent_obj, "crs_id", $crs_obj_id);
		$this->setstatus_link = $this->gCtrl->getLinkTarget($this->parent_obj, "listStatus");
		$this->overnights_link = $this->gCtrl->getLinkTarget($this->parent_obj, "showOvernights");
		$this->bookings_link = $this->gCtrl->getLinkTarget($this->parent_obj, "viewBookings");
		$this->gCtrl->clearParameters($this->parent_obj);

		$this->gCtrl->setParameterByClass("ilObjCourseGUI", "ref_id", $crs_ref_id);
		$this->cancel_training_link = $this->gCtrl->getLinkTargetByClass("ilObjCourseGUI","confirmTrainingCancellation");
		$this->gCtrl->clearParametersByClass("ilObjCourseGUI");

		$this->gCtrl->setParameterByClass("gevTrainerMailHandlingGUI", "crs_id", $crs_obj_id);
		$this->maillog = $this->gCtrl->getLinkTargetByClass("gevTrainerMailHandlingGUI", "showLog");
		$this->gCtrl->clearParametersByClass("gevTrainerMailHandlingGUI");

		$this->gCtrl->setParameterByClass("ilObjCourseGUI", "ref_id", $crs_ref_id);
		$this->edit_settings_link = $this->gCtrl->getLinkTargetByClass("ilObjCourseGUI", "edit");
		$this->gCtrl->clearParametersByClass("ilObjCourseGUI");

		$this->gCtrl->setParameterByClass("ilparticipationstatusgui", "ref_id", $ref_id);
		$this->get_attendance_list_link = $this->gCtrl->getLinkTargetByClass(array('ilparticipationstatusadmingui','ilparticipationstatusgui'), "viewAttendanceList");
		$this->gCtrl->setParameterByClass("ilparticipationstatusgui", "ref_id", null);
	}

	protected function getCourseLink($crs_obj_id, $crs_ref_id) {
		$crs_utils = gevCourseUtils::getInstance($crs_obj_id);

		if($crs_utils->userHasPermissionTo($this->user_id, "write")){
			$this->gCtrl->setParameterByClass("ilRepositoryGUI", "ref_id", $crs_ref_id);
			$info_crs_link = $this->gCtrl->getLinkTargetByClass("ilRepositoryGUI", "");
			$this->gCtrl->clearParametersByClass("ilRepositoryGUI");
			return $info_crs_link;
		} else if($crs_utils->userHasPermissionTo($this->user_id, "write_reduced_settings")) {
			$this->gCtrl->setParameterByClass("gevDecentralTrainingGUI", "ref_id", $crs_ref_id);
			$small_settings_link = $this->gCtrl->getLinkTargetByClass("gevDecentralTrainingGUI", "showSettings");
			$this->gCtrl->clearParametersByClass("gevDecentralTrainingGUI");
			return $small_settings_link;
		} else {
			$this->gCtrl->setParameterByClass("ilObjCourseGUI", "ref_id", $crs_ref_id);
			$small_settings_link = $this->gCtrl->getLinkTargetByClass("ilObjCourseGUI", "view");
			$this->gCtrl->clearParametersByClass("ilObjCourseGUI");
			return $small_settings_link;
		}
	}

	protected function statusPicture($status) {
		switch($status) {
			case gevMyTrainingsAdmin::CLOSED:
				return $this->closed_img;
				break;
			case gevMyTrainingsAdmin::WIP:
				return $this->wip_img;
				break;
			case gevMyTrainingsAdmin::NOT_FINISHED:
				return $this->not_closed_img;
				break;
			default:
				throw new Exception("gevMyTrainingsAdminTableGUI::statusPicture: Unknown Training State");
		}
	}
}