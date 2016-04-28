<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* My Trainings Admin Appointments GUI for Generali
*
* @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevMyTrainingsAdminGUI: ilParticipationStatusAdminGUI
* @ilCtrl_Calls gevMyTrainingsAdminGUI: gevDesktopGUI
* @ilCtrl_Calls gevMyTrainingsAdminGUI: gevTrainerMailHandlingGUI
*
*/

require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsAdminTableGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
require_once("Services/GEV/Utils/classes/class.gevMyTrainingsAdmin.php");

class gevMyTrainingsAdminGUI {

	public $crs_ref_id;

	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;
		
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gUser = $ilUser;
		$this->gLog = $ilLog;

		$this->helper = new gevMyTrainingsAdmin($ilUser->getId());
		$this->crs_ref_id = false;
	}
	
	public function executeCommand() {
		$cmd = $this->gCtrl->getCmd();
		if (!$cmd) {
			$cmd = "view";
		}
		
		$in_search = false;
		if($cmd == "search") {
			$in_search = true;
			$cmd = "view";
		}

		switch ($cmd) {
			case "view":
				$cont = $this->view($in_search);
				break;
			case "memberList":
			case "showOvernights":
			case "saveOvernights":
			case "viewBookings":
			case "backFromBookings":
			case "saveSendMailDate":
			case "saveFilterInputs":
				$cont = $this->$cmd();
				break;

			//participation-status commands	
			//case "setParticipationStatus":
			case "listStatus":
			case  "listParticipationStatus":
				$cont = $this->listParticipationStatus();
				break;

			case "finalize":
			case "confirmFinalize":
			case "saveStatusAndPoints":
			case "uploadAttendanceList":
			case "viewAttendanceList":
				//ilParticipationStatusTableGUI
				require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
				$crs_ref_id = $this->getCrsRefId();
				$gui = ilParticipationStatusAdminGUI::getInstanceByRefId($crs_ref_id);
				
				$gui->from_foreign_class = 'gevMyTrainingsAdminGUI';
				$gui->crs_ref_id = $crs_ref_id;
				$this->gCtrl->setParameter($gui, "crsrefid", $crs_ref_id);

				//$gui->returnToList();
				//die('forwarding cmd');
				$ret = $this->gCtrl->forwardCommand($gui);
				break;
			case "showLog":
			case "selectMailToMembersRecipients":
			case "showMailToMembersMailInput":
			case "sendMailToMembers":
				require_once("Services/GEV/Mailing/classes/class.gevTrainerMailHandlingGUI.php");
				$gui = new gevTrainerMailHandlingGUI($this);
				$ret = $this->gCtrl->forwardCommand($gui);
				break;
			case "showSettings":
				require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingGUI.php");
				$gui = new gevDecentralTrainingGUI();
				$ret = $this->gCtrl->forwardCommand($gui);
				break;
			default:
				$errstr = "gevMyTrainingsAdminGUI: Unknown command '".$cmd."'";
				$this->gLog->write($errstr);
				throw new ilException($errstr);
		}
		
		if ($cont) {
			$this->gTpl->setContent($cont);
		}
	}

	public function getCrsRefId() {
		$crs_ref_id = $_GET['crsrefid'];

		if(!$crs_ref_id) {
			$crs_ref_id = $_GET['ref_id'];
		}

		if(!$crs_ref_id){
			throw new ilException("gevMyTrainingsAdminGUI - needs course-refid");
		}
		return $crs_ref_id;
	}

	public function view() {
		$filter_form = new catFilterFlatViewGUI($this, $this->helper->filter(), $this->helper->displayFilter(), "saveFilterInputs");
		$trainings_table = new gevMyTrainingsAdminTableGUI($this->gUser->getId(), $this, $filter_form);

		return $trainings_table->getHTML();
	}

	public function saveFilterInputs() {
		$this->helper->saveFilterInputs();
		return $this->view();
	}

	public function helper() {
		return $this->helper;
	}

	public function memberList() {
		
		$crs_ref_id = $this->getCrsRefId();
		$this->gCtrl->redirect($this, "view");
	}

	static public function renderListParticipationStatus($a_parent_gui, $a_back_target, $a_crs_ref_id) {
		global $ilTabs, $ilCtrl, $lng;
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"), $a_back_target);
		//ilParticipationStatusTableGUI
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
		require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusTableGUI.php");
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$crs_obj = new ilObjCourse(intval($a_crs_ref_id));

		$title = new catTitleGUI(sprintf($lng->txt("gev_set_course_status_title"), $crs_obj->getTitle())
								, $lng->txt("gev_set_course_status_title_desc")
								, "GEV_img/ico-head-edubio.png"
								, false);
		$spacer = new catHSpacerGUI();
		
		$lng->loadLanguageModule("ptst");

		$ptstatus_admingui =  ilParticipationStatusAdminGUI::getInstanceByRefId($a_crs_ref_id);
		//$ptstatus_admingui =  new ilParticipationStatusAdminGUI($crs_obj);
		$may_write = $ptstatus_admingui->mayWrite();
		$pstatus = $ptstatus_admingui->getParticipationStatus();
		if($pstatus->getMode() == ilParticipationStatus::MODE_CONTINUOUS)
		{
			$may_finalize = false;
		}
		else
		{
			$may_finalize = $may_write;
		}
		$ptstatusgui = new ilParticipationStatusTableGUI($a_parent_gui, 'listParticipationStatus', $crs_obj, $may_write, $may_finalize);
		
		$form_action = $ptstatusgui->getFormAction();
		$form_action .= '&crsrefid=' .$a_crs_ref_id;
		$ptstatusgui->setFormAction($form_action);

		$ilCtrl->setParameter($a_parent_gui, "crsrefid", $a_crs_ref_id);
		ilParticipationStatusAdminGUI::renderToolbar($a_parent_gui, $pstatus, $crs_obj, $may_write, $may_finalize);
		global $ilToolbar;

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_utils = gevCourseUtils::getInstanceByObj($crs_obj);
		$min_parti = ($crs_utils->getMinParticipants() === null) ? 0 : $crs_utils->getMinParticipants();
		$succ_parti = $crs_utils->getSuccessfullParticipants();

		$getSuccessfullParticipants = "";
		if($min_parti > count($succ_parti)) {
			$tpl_adivce = new ilTemplate("tpl.gev_my_advice.html", true, true, "Services/GEV/Desktop");
			$tpl_adivce->setCurrentBlock("advice");
			$tpl_adivce->setVariable("ADVICE", sprintf($lng->txt("gev_training_min_participation_count_not_reached"),$min_parti));
			$tpl_adivce->parseCurrentBlock();

			$getSuccessfullParticipants = $tpl_adivce->get();
		}
		
		$ret = ( $title->render()
			   . $ilToolbar->getHTML()
			   . $spacer->render()
			   . $getSuccessfullParticipants
			   . $ptstatusgui->getHTML()
			   );
		$ilToolbar->setHidden(true);
		return $ret;
	}

	protected function listParticipationStatus() {
		global $ilCtrl;
		return static::renderListParticipationStatus($this, $ilCtrl->getLinkTarget($this, "view"), $this->getCrsRefId());
	}
	
	protected function checkAccomodation($crs_utils) {
		if (!$crs_utils->isWithAccomodations()) {
			ilUtil::sendFailure($this->gLng->txt("gev_mytrainingsap_no_accomodations"), true);
			$this->gCtrl->redirect($this, "view");
		}
	}
	
	protected function checkIsTrainer($crs_utils) {
		if (!in_array($this->gUser->getId(), $crs_utils->getTrainers())) {
			ilUtil::redirect("index.php");
		}
	}

	public static function renderShowOvernights($a_parent_gui, $a_backlink_target, $a_user_id, $a_crs_utils, $a_form = null) {
		global $ilTabs, $ilCtrl, $lng;
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"), $a_backlink_target);

		$title = new catTitleGUI(sprintf($lng->txt("gev_edit_overnights"), $a_crs_utils->getTitle())
								, $lng->txt("gev_edit_overnights_desc")
								, "GEV_img/ico-head-edit.png"
								, false
								);

		if ($a_form === null) {
			$ilCtrl->setParameter($a_parent_gui, "crs_id", $a_crs_utils->getId());
			$a_form = static::buildOvernightsForm($a_user_id, $a_crs_utils, $ilCtrl->getFormAction($a_parent_gui));
		}
		
		return    $title->render()
				. $a_form->getHTML();
	}

	protected function showOvernights($a_form = null) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = intval($_GET["crs_id"]);
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		$this->checkAccomodation($crs_utils);
		$this->checkIsTrainer($crs_utils);
		
		return static::renderShowOvernights($this, $this->gCtrl->getLinkTarget($this, "view"), $this->gUser->getId(), $crs_utils, $a_form);
	}
	
	protected function saveOvernights() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = intval($_GET["crs_id"]);
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		$this->checkAccomodation($crs_utils);
		$this->checkIsTrainer($crs_utils);
		
		$this->gCtrl->setParameter($this, "crs_id", $crs_id);
		$form = static::buildOvernightsForm($this->gUser->getId(), $crs_utils, $this->gCtrl->getFormAction($this));
		if ($form->checkInput()) {
			ilSetAccomodationsGUI::importAccomodationsFromForm($form, $crs_id, $this->gUser->getId());
			ilUtil::sendSuccess($this->gLng->txt("gev_mytrainingsap_saved_overnights"));
		}
		return $this->showOvernights($form);	
	}
	
	public static function buildOvernightsForm($a_user_id, $a_crs_utils, $a_form_action) {
		global $lng;
		
		require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
		require_once("Services/Accomodations/classes/class.ilSetAccomodationsGUI.php");
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_overnights_form.html", "Services/GEV/Desktop");
		//$form->setTitle($a_crs_utils->getTitle());
		$form->addCommandButton("saveOvernights", $lng->txt("save"));
		
		$lng->loadLanguageModule("acco");
		ilSetAccomodationsGUI::addAccomodationsToForm($form, $a_crs_utils->getId(), $a_user_id);
		if ($_POST["acco"]) {
			$form->getItemByPostVar("acco")->setValue($_POST["acco"]);
		}
		
		$form->setFormAction($a_form_action);

		return $form;
	}
	
	protected function viewBookings() {
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::setBackTarget(
			$this->gCtrl->getLinkTargetByClass(array("gevDesktopGUI", "gevMyTrainingsApGUI"), "backFromBookings")
			);
		
		$this->gCtrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $_GET["crsrefid"]);
		$this->gCtrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
	}
	
	protected function backFromBookings() {
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::removeBackTarget();
		return $this->view();
	}
	
	protected function saveSendMailDate() {
		$ptstatus_admingui =  ilParticipationStatusAdminGUI::getInstanceByRefId($this->getCrsRefId());
		$crs_obj = new ilObjCourse(intval($this->getCrsRefId()));
		$pstatus = $ptstatus_admingui->getParticipationStatus();
		
		if (!array_key_exists("mail_send_confirm", $_POST) || !$ptstatus_admingui->mayWrite()) {
			ilUtil::sendFailure($this->gLng->txt("gev_psstatus_mail_send_date_error"), true);
		}
		else {
			$d = $_POST["mail_send_at"]["date"];
			$date_set = $d["y"]."-".str_pad($d["m"], 2, '0', STR_PAD_LEFT)."-".str_pad($d["d"], 2, '0', STR_PAD_LEFT);
			
			$helper = ilParticipationStatusHelper::getInstance($crs_obj);
			$date_tr = $helper->getCourseStart();
			$date_tr->increment(ilDateTime::DAY, -3);
			if ($date_tr->get(IL_CAL_DATE) < $date_set) {
				ilUtil::sendFailure($this->gLng->txt("gev_psstatus_mail_send_date_invalid"), true);
			}
			else {
				$pstatus->setMailSendDate($date_set);
				ilUtil::sendSuccess($this->gLng->txt("gev_psstatus_mail_send_date_success"), true);
			}
		}
		return $this->listParticipationStatus();
	}
}