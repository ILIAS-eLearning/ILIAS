<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Table showing courses the user is tutoring.
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/CaTUIComponents/classes/class.catAccordionTableGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");

require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/CourseBooking/classes/class.ilCourseBooking.php");
require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
require_once "./Services/ParticipationStatus/classes/class.ilParticipationStatusHelper.php";

require_once("Services/Calendar/classes/class.ilDatePresentation.php");
//require_once("Services/Calendar/classes/class.ilDate.php");

class gevMyTrainingsApTableGUI extends catAccordionTableGUI {
	public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd="", $a_template_context="") {
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		global $ilCtrl, $lng;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;

		$user_util = gevUserUtils::getInstance($a_user_id);
		$this->user_id = $a_user_id;

		$this->setEnableTitle(true);
		$this->setTitle("gev_mytrainingsap_title");
		$this->setSubtitle("gev_mytrainingsap_title_desc");
		$this->setImage("GEV_img/ico-head-my-training-deployments.png");
	
		$this->memberlist_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-eye.png").'" />';
		$this->setstatus_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-table-state-neutral.png").'" />';
		
		$legend = new catLegendGUI();
		$legend->addItem($this->memberlist_img, "gev_mytrainingsap_legend_memberlist")
			   ->addItem($this->setstatus_img, "gev_mytrainingsap_legend_setstatus");
		$this->setLegend($legend);

		
		$this->setRowTemplate("tpl.gev_my_trainingsap_row.html", "Services/GEV/Desktop");

		//$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "view"));
		
		$this->setExternalSegmentation(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);

		$this->addColumn("", "expand", "0px", false, "catTableExpandButton");

		$this->addColumn($this->lng->txt("title"), "title");
		$this->addColumn($this->lng->txt("gev_training_id"), "custom_id");
		$this->addColumn($this->lng->txt("gev_learning_type"), "type");
		//$this->addColumn($this->lng->txt("gev_learning_cat"), "category");
		$this->addColumn($this->lng->txt("gev_location"), "location");
		$this->addColumn($this->lng->txt("date"), "date", "112px");
		$this->addColumn($this->lng->txt("apdays"), "apdays");
		$this->addColumn($this->lng->txt("mbrcount"), "mbrcount");
		$this->addColumn('<img src="'.ilUtil::getImagePath("gev_action.png").'" />', "actions", "30px", false);

		$data = $user_util->getMyAppointmentsCourseInformation();

		$this->setData($data);
	}

	protected function fillRow($a_set) {
		$this->tpl->setVariable("ACCORDION_BUTTON_CLASS", $this->getAccordionButtonExpanderClass());
		$this->tpl->setVariable("ACCORDION_ROW", $this->getAccordionRowClass());
		$this->tpl->setVariable("COLSPAN", $this->getColspan());


		$target_groups_str = '<ul>';
		foreach ($a_set['target_group'] as $tg){
			$target_groups_str .= '<li>' .$tg .'</li>';
		}
		$target_groups_str .= '</ul>';


		if ($a_set["start_date"] == null || $a_set["end_date"] == null) {
			if ($a_set["scheduled_for"] == null) {
				$date = $this->lng->txt("gev_table_no_entry");
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
		
		$apdays = array();
		foreach ($a_set['apdays'] as $tday) {
			$apdays[] =  ilDatePresentation::formatDate($tday);
		}
		$apdays_str = join('<br>', $apdays);

		
		$mbrs = $a_set['mbr_booked'] .' (' .$a_set['mbr_waiting'] .')'
				.' / ' .$a_set['mbr_min'] .'-' .$a_set['mbr_max'];

		
		$this->ctrl->setParameterByClass("gevMemberListDeliveryGUI", "ref_id", $a_set["crs_ref_id"]);
		$memberlist_link = $this->ctrl->getLinkTargetByClass("gevMemberListDeliveryGUI", "trainer");
		$this->ctrl->clearParametersByClass("gevMemberListDeliveryGUI");
		
		$this->ctrl->setParameter($this->parent_obj, "crsrefid", $a_set['crs_ref_id']);
		$setstatus_link = $this->ctrl->getLinkTarget($this->parent_obj, "listStatus");
		$this->ctrl->clearParameters($this->parent_obj);

		$actions = "<a href=\"".$memberlist_link."\">".$this->memberlist_img."</a>";
		if($a_set['may_finalize']) {
			$actions .='&nbsp;' ."<a href=\"".$setstatus_link."\">".$this->setstatus_img."</a>";
		}

		$this->tpl->setVariable("TITLE", $a_set["title"]);
		$this->tpl->setVariable("CUSTOM_ID", $a_set["custom_id"]);
		$this->tpl->setVariable("TYPE", $a_set["type"]);
		//$this->tpl->setVariable("CATEGORY", $a_set["category"]);
		$this->tpl->setVariable("LOCATION", $a_set["location"]);
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
		$this->tpl->setVariable("MBMRLST_LINK_TXT", $this->lng->txt('gev_mytrainingsap_btn_memberlist'));
		if ($a_set['may_finalize']) {
			$this->tpl->setCurrentBlock("set_stat");
			$this->tpl->setVariable("SETSTAT_LINK", $setstatus_link);
			$this->tpl->setVariable("SETSTAT_LINK_TXT", $this->lng->txt('gev_mytrainingsap_btn_setstatus'));
			$this->tpl->parseCurrentBlock();
		}

	}
}

?>