<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* My Trainings Appointments GUI for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevMyTrainingsApGUI: ilParticipationStatusAdminGUI
*
*/

require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Services/GEV/Desktop/classes/class.gevMyTrainingsApTableGUI.php");
require_once("Services/Utilities/classes/class.ilUtil.php");

class gevMyTrainingsApGUI {

	public $crs_ref_id;

	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->user = &$ilUser;
		$this->log = &$ilLog;
	
		$this->crs_ref_id = false;

	}
	
	public function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		if (!$cmd) {
			$cmd = "view";
		}
		
		switch ($cmd) {
			case "view":
			case "memberList":
			case "showOvernights":
			case "saveOvernights":
			case "viewBookings":
			case "backFromBookings":
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
				//ilParticipationStatusTableGUI
				require_once("Services/ParticipationStatus/classes/class.ilParticipationStatusAdminGUI.php");
				$crs_ref_id = $this->getCrsRefId();
				$gui = ilParticipationStatusAdminGUI::getInstanceByRefId($crs_ref_id);
				
				$gui->from_foreign_class = 'gevMyTrainingsApGUI';
				$gui->crs_ref_id = $crs_ref_id;

				//$gui->returnToList();
				//die('forwarding cmd');
				$ret = $this->ctrl->forwardCommand($gui);
				break;

			default:
				$errstr = "gevMyTrainingsApGUI: Unknown command '".$cmd."'";
				$this->log->write($errstr);
				throw new ilException($errstr);
		}
		
		if ($cont) {
			$this->tpl->setContent($cont);
		}
	}
	


	public function getCrsRefId() {
		$crs_ref_id = $_GET['crsrefid'];

		if(! $crs_ref_id){
			throw new ilException("gevMyTrainingsApGUI - needs course-refid");
		}
		return $crs_ref_id;
	}





	// std-view, my trainings-ap-table;
	public function view() {
		$trainings_table = new gevMyTrainingsApTableGUI($this->user->getId(), $this);
		return (
			$trainings_table->getHTML()
		);
	}


	public function memberList() {
		
		$crs_ref_id = $this->getCrsRefId();
		$this->ctrl->redirect($this, "view");
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
		if($ptstatus_admingui->getParticipationStatus()->getMode() == ilParticipationStatus::MODE_CONTINUOUS)
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

		return (
				$title->render()
			   .$spacer->render()
			   .$ptstatusgui->getHTML()
			   );
	}

	protected function listParticipationStatus() {
		global $ilCtrl;
		return static::renderListParticipationStatus($this, $ilCtrl->getLinkTarget($this, "view"), $this->getCrsRefId());
	}
	
	protected function checkAccomodation($crs_utils) {
		if (!$crs_utils->isWithAccomodations()) {
			ilUtil::sendFailure($this->lng->txt("gev_mytrainingsap_no_accomodations"), true);
			$this->ctrl->redirect($this, "view");
		}
	}
	
	protected function checkIsTrainer($crs_utils) {
		if (!in_array($this->user->getId(), $crs_utils->getTrainers())) {
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
			$a_form = static::buildOvernightsForm($a_user_id, $a_crs_utils, $ilCtrl->getFormAction($a_parent_gui));
		}
		
		return    $title->render()
				. $a_form->getHTML();
	}

	protected function showOvernights($a_form = null) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = $_GET["crs_id"];
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		$this->checkAccomodation($crs_utils);
		$this->checkIsTrainer($crs_utils);
		
		return static::renderShowOvernights($this, $this->ctrl->getLinkTarget($this, "view"), $this->user->getId(), $crs_utils, $a_form);
	}
	
	protected function saveOvernights() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_id = $_GET["crs_id"];
		$crs_utils = gevCourseUtils::getInstance($crs_id);
		
		$this->checkAccomodation($crs_utils);
		$this->checkIsTrainer($crs_utils);

		$form = $this->buildOvernightsForm($crs_id, $crs_utils);
		if ($form->checkInput()) {
			ilSetAccomodationsGUI::importAccomodationsFromForm($form, $crs_id, $this->user->getId());
			ilUtil::sendSuccess($this->lng->txt("gev_mytrainingsap_saved_overnights"));
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
			$this->ctrl->getLinkTargetByClass(array("gevDesktopGUI", "gevMyTrainingsApGUI"), "backFromBookings")
			);
		
		$this->ctrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $_GET["crsrefid"]);
		$this->ctrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
	}
	
	protected function backFromBookings() {
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::removeBackTarget();
		return $this->view();
	}
}

?>