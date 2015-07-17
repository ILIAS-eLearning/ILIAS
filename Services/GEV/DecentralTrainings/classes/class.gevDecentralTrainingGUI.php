<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Forms for decentral trainings.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");

class gevDecentralTrainingGUI {
	const AUTO_RELOAD_TIMEOUT_MS = 5000;
	
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog, $ilAccess;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->current_user = &$ilUser;
		$this->access = &$ilAccess;
		$this->user_id = null;
		$this->date = null;
		$this->open_creation_requests = null;

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->loadUserId();
		$this->loadDate();
		
		$cmd = $this->ctrl->getCmd();
		
		switch($cmd) {
			case "chooseTemplateAndTrainers":
			case "createTraining":
			case "finalizeTrainingCreation":
			case "cancel":
			case "backFromBooking":
			case "showSettings":
			case "updateSettings":
			case "showOpenRequests":
				$cont = $this->$cmd();
				break;
			default:
				$this->log->write("gevDecentralTrainingGUI: Unknown command '".$cmd."'");
		}
		
		
		if ($cont) {
			$this->tpl->setContent($cont);
		}
	}
	
	protected function loadUserId() {
		if ($_GET["user_id"] === null) {
			return;
		}
		
		$this->user_id = intval($_GET["user_id"]);
		
		if ( $this->user_id !== null 
		&&  !gevDecentralTrainingUtils::getInstance()->canCreateFor($this->current_user->getId(), $this->user_id)) {
			throw new Exception( "gevDecentralTrainingGUI::loadUserId: No permission of ".$this->current_user->getId()
								." to create training for ".$this->user_id);
		}
	}
	
	protected function loadDate() {
		$this->date = $_GET["date"];
	}
	
	protected function getRequestDB() {
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		return $dec_utils->getCreationRequestDB();
	}
	
	protected function getOpenCreationRequests() {
		if ($this->open_creation_requests === null) {
			$db = $this->getRequestDB();
			$this->open_creation_requests = $db->openRequestsOfUser((int)$this->current_user->getId());
		}
		return $this->open_creation_requests;
	}
	
	protected function getWaitingTime() {
		$db = $this->getRequestDB();
		return $db->waitingTimeInMinuteEstimate();
	}
	
	protected function lastCreatedCourseId() {
		$db = $this->getRequestDB();
		return $db->lastCreatedTrainingOfUser();
	}
	
	protected function flushOpenCreationRequests() {
		$this->open_creation_requests = null;
	}
	
	protected function userCanOpenNewCreationRequest() {
		if ($this->userCanOpenMultipleRequests()) {
			return true;
		}
		return count($this->getOpenCreationRequests()) === 0;
	}
	
	protected function userCanOpenMultipleRequests() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($this->current_user->getId());
		return $user_utils->isAdmin();
	}
	
	protected function cancel() {
		$this->ctrl->redirectByClass("ilTEPGUI");
	}
	
	protected function getOpenRequestsView(array $a_requests, $a_do_autoload = false) {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$tpl = new ilTemplate("tpl.open_requests.html", true, true, "Services/GEV/DecentralTrainings");
		
		$tpl->setCurrentBlock("header");
		$tpl->setVariable("HEADER", $this->lng->txt("gev_dec_training_open_requests_header"));
		$tpl->parseCurrentBlock();
		
		if (count($a_requests) > 0) {
			$tpl->setCurrentBlock("requests");
			foreach ($a_requests as $request) {
				$tpl->setCurrentBlock("request");
				$tpl->setVariable("TITLE", ilObject::_lookupTitle($request->templateObjId()));
				$settings = $request->settings();
				$start = explode(", ", ilDatePresentation::formatDate($settings->start()));
				$tpl->setVariable("DATE", $start[0]);
				$tpl->setVariable("START_TIME", $start[1]);
				$end = explode(" ", ilDatePresentation::formatDate($settings->end()));
				$tpl->setVariable("END_TIME", $end[1]);
				$tpl->parseCurrentBlock();
			}
			$tpl->parseCurrentBlock();
		}
		else {
			$tpl->touchBlock("no_requests");
		}

		$tpl->setCurrentBlock("footer");
		$wait_m = $this->getWaitingTime();
		$time_info = sprintf($this->lng->txt("gev_dec_training_open_requests_time_info"), $wait_m);
		$tpl->setVariable("FOOTER", $time_info);
		$tpl->parseCurrentBlock();

		if ($a_do_autoload) {
			$tpl->setCurrentBlock("autoreload");
			$tpl->setVariable("TIMEOUT", self::AUTO_RELOAD_TIMEOUT_MS);
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}
	
	protected function showOpenRequests() {
		$requests = $this->getOpenCreationRequests();
		if ($this->userCanOpenNewCreationRequest() && !$this->userCanOpenMultipleRequests()) {
			return $this->redirectToBookingFormOfLastCreatedTraining();
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($this->current_user->getId());
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$view = $this->getOpenRequestsView($requests, !$this->userCanOpenNewCreationRequest());
		
		return  $title->render()
			  . $view;
	}
	
	protected function showOpenRequestsAsNotice() {
		$requests = $this->getOpenCreationRequests();
		if (count($requests) > 0) {
			$view = $this->getOpenRequestsView($requests);
			ilUtil::sendInfo($view);
		}
	}
	
	protected function chooseTemplateAndTrainers($a_form = null) {
		if (!$this->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		$this->showOpenRequestsAsNotice();
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$form = ($a_form === null) ? $this->buildChooseTemplateAndTrainersForm($this->user_id, $this->date)
								   : $a_form;
		
		return   $title->render()
				.$form->getHTML();
	}
	
	protected function createTraining($a_form = null) {
		if (!$this->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		$this->showOpenRequestsAsNotice();
		
		$form_prev = $this->buildChooseTemplateAndTrainersForm($this->user_id, $this->date);
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		if (!$form_prev->checkInput()) {
			return $this->chooseTemplateAndTrainers($form_prev);
		}
		foreach ($form_prev->getInput("trainers") as $trainer_id) {
			if (!$dec_utils->canCreateFor($this->current_user->getId(), $trainer_id)) {
				throw new Exception( "gevDecentralTrainingGUI::createTraining: No permission for ".$this->current_user->getId()
									." to create training for ".$trainer_id);
			}
		}
		if (count($form_prev->getInput("trainers")) == 0) {
			$form_prev->getItemByPostvar("trainers")->setAlert($this->lng->txt("gev_dec_training_choose_min_one_trainer"));
			return $this->chooseTemplateAndTrainers($form_prev);
		}
		$ltype = $form_prev->getInput("ltype");
		$template_id = $form_prev->getInput($ltype."_template");
		$trainer_ids = $form_prev->getInput("trainers");
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");

		$form = ($a_form === null) ? $this->buildTrainingOptionsForm(true, null, $trainer_ids, $this->date, $template_id) 
								   : $a_form;
		
		$form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		return   $title->render()
				.$form->getHTML();
	}

	protected function failCreateTraining($a_form) {
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");

		$tmplt_id = new ilHiddenInputGUI("template_id");
		$a_form->addItem($tmplt_id);
				
		$trnrs = new ilHiddenInputGUI("trainer_ids");
		$a_form->addItem($trnrs);

		$a_form->setValuesByPost();
		$a_form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		$a_form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$a_form->setFormAction($this->ctrl->getFormAction($this));

		return   $title->render()
				.$a_form->getHTML();
	}
	
	protected function finalizeTrainingCreation() {
		if (!$this->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequest.php");
		
		$form_prev = $this->buildTrainingOptionsForm(false);
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		if (!$form_prev->checkInput()) {
			return $this->createTraining($form_prev);
		}
		
		$template_id = intval($form_prev->getInput("template_id"));
		if(!$this->checkDecentralTrainingConstraints($form_prev, $template_id)) {
			return $this->failCreateTraining($form_prev);
		}
		
		$trainer_ids = array_map(function($inp) {return (int)$inp; }
								, unserialize(base64_decode($form_prev->getInput("trainer_ids")))
								);
		
		$crs_utils = gevCourseUtils::getInstance($template_id);
		$settings = $this->getSettingsFromForm($crs_utils, $form_prev);
		$creation_request = new gevDecentralTrainingCreationRequest
									( $dec_utils->getCreationRequestDB()
									, (int)$this->current_user->getId()
									, (int)$template_id
									, $trainer_ids
									, $settings
									);
		$creation_request->request();
		$this->flushOpenCreationRequests();
		
		ilUtil::sendSuccess($this->lng->txt("gev_dec_training_creation_requested"), true);
		
		if (!$this->userCanOpenNewCreationRequest()) {
			$this->ctrl->redirect($this, "showOpenRequests");
		}
		else {
			$this->ctrl->redirectByClass(array("ilTEPGUI"));
		}
	}
	
	protected function redirectToBookingFormOfLastCreatedTraining() {
		$obj_id = $this->lastCreatedCourseId();
		if (!$obj_id) {
			$this->ctrl->redirectByClass(array("ilTEPGUI"));
		}
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$ref_id = gevObjectUtils::getRefId($obj_id);
		
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingPermissions.php");
		
		$this->ctrl->setParameter($this, "obj_id", $obj_id);
		ilCourseBookingAdminGUI::setBackTarget($this->ctrl->getLinkTarget($this, "backFromBooking"));
		$this->ctrl->setParameter($this, "obj_id", null);
		
		$this->ctrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $ref_id);
		$this->ctrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
	}
	
	protected function backFromBooking() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		ilCourseBookingAdminGUI::setBackTarget(null);
		
		$this->ctrl->redirectByClass(array("ilTEPGUI"));
		return;
	}
	
	protected function showSettings($a_form = null) {
		if ($a_form === null) {
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
			$ref_id = intval($_GET["ref_id"]);
			if ($ref_id == null) {
				throw new Exception("gevDecentralTrainingGUI::modifySettings: no ref id");
			}
			$obj_id = gevObjectUtils::getObjId($ref_id);
			$form = $this->buildTrainingOptionsForm(true, $obj_id);
		}
		else {
			$form = $a_form;
			$obj_id = intval($_POST["obj_id"]);
		}
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$form->addCommandButton("updateSettings", $this->lng->txt("save"));
		$form->addCommandButton("cancel", $this->lng->txt("back"));
		$form->setFormAction($this->ctrl->getFormAction($this));
				
		$title = new catTitleGUI("gev_dec_training_settings_header"
								, "gev_dec_training_settings_header_note"
								, "GEV_img/ico-head-create-decentral-training.png"
								);
	
	
		return   $title->render()
				.$form->getHTML();
	}
	
	protected function updateSettings() {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		$form = $this->buildTrainingOptionsForm(false, $_POST["obj_id"]);

		//gev patch start
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->showSettings($form);
		}
		
		if(!$this->checkDecentralTrainingConstraints($form_prev, intval($_POST["obj_id"]))) {
			return $this->showSettings($form);
		}
		
		if (!$this->access->checkAccess("write_reduced_settings", "", gevObjectUtils::getRefId($_POST["obj_id"]))) {
			$this->log->write("gevDecentralTrainingGUI::updateSettings: User ".$this->current_user->getId()
							 ." tried to update Settings but has no permission.");
			throw new Exception("gevDecentralTrainingGUI::updateSettings: no permission");
		}
		
		$crs_utils = gevCourseUtils::getInstance($_POST["obj_id"]);
		$settings = $this->getSettingsFromForm($crs_utils, $form);
		$settings->applyTo((int)$_POST["obj_id"]);
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
		return $this->showSettings($form);
	}
	
	protected function getSettingsFromForm(gevCourseUtils $a_target, $a_form) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingSettings.php");
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
	
		$time = $a_form->getInput("time");
		$start_date = $a_form->getInput("date");
		$start_date = $start_date["date"];
		
		$start_datetime = new ilDateTime($start_date." ".$time["start"]["time"], IL_CAL_DATETIME);
		$end_datetime = new ilDateTime($start_date." ".$time["end"]["time"], IL_CAL_DATETIME);
		
		if ($a_target->isPraesenztraining()) {
			$venue = $a_form->getInput("venue");
			$venue_obj_id = $venue ? $venue : null;
			
			if (!$venue_obj_id) {
				$venue_free_text = $a_form->getInput("venue_free_text");
				$venue_text = $venue_free_text ? $venue_free_text : null;
			}
		}
		else {
			$venue_obj_id = null;
			$venue_text = null;
		}
		
		if ($a_target->isWebinar()) {
			$link = $a_form->getInput("webinar_link");
			$webinar_link = $link ? $link : null;
			$password = $a_form->getInput("webinar_password");
			$webinar_password = $password ? $password : null;
		}
		else {
			$webinar_link = null;
			$webinar_password = null;
		}
		
		$orgu_id = $a_form->getInput("orgu_id");
		$description = $a_form->getInput("description");
		$orgaInfo = $a_form->getInput("orgaInfo");
		
		return new gevDecentralTrainingSettings
						( $start_datetime
						, $end_datetime
						, $venue_obj_id ? (int)$venue_obj_id : null
						, $venue_text
						, $orgu_id ? (int)$orgu_id : null
						, $description ? $description : ""
						, $orgaInfo ? $orgaInfo : ""
						, $webinar_link
						, $webinar_password
						);
	}
	
	protected function buildChooseTemplateAndTrainersForm($a_user_id = null, $a_date = null) {
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		
		$dec_utils = gevDecentralTrainingUtils::getInstance();

		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_choose_template_and_trainers"));
		$form->addCommandButton("createTraining", $this->lng->txt("continue"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$this->ctrl->setParameter($this, "date", $this->date);
		$form->setFormAction($this->ctrl->getFormAction($this));
		
		// choice of template
		
		$templates = array();
		$key = null;
		$_templates = $dec_utils->getAvailableTemplatesFor($this->current_user->getId());
		
		if (count($_templates) == 0) {
			ilUtil::sendFailure($this->lng->txt("gev_dec_training_no_templates"), true);
			$this->ctrl->redirectByClass("ilTEPGUI");
		}
		
		foreach ($_templates as $obj_id => $info) {
			if (!$info["ltype"]) {
				// Only use templates with a learning type
				continue;
			}
			if (!array_key_exists($info["ltype"], $templates)) {
				$templates[$info["ltype"]] = array();
			}
			if ($key === null) {
				$key = strtolower(str_replace(" ", "_", $info["ltype"]));
			}
			$templates[$info["ltype"]][$info["obj_id"]] = $info["title"];
		}
		
		$ltype_choice = new ilRadioGroupInputGUI($this->lng->txt("gev_course_type"), "ltype");
		$form->addItem($ltype_choice);
		foreach ($templates as $ltype => $tmplts) {
		//foreach (array("Präsenztraining", "Webinar") as $ltype) {
			//$tmplts = $templates[$ltype];
			$key = strtolower(str_replace(" ", "_", $ltype));
			$ltype_opt = new ilRadioOption($ltype, $key);
			$ltype_choice->addOption($ltype_opt);
			
			$training_select = new ilSelectInputGUI($this->lng->txt("gev_dec_training_template"), $key."_template");
			$training_select->setOptions($tmplts);

			$ltype_opt->addSubItem($training_select);
		}
		$ltype_choice->setValue("präsenztraining");
		
		
		// maybe choice of trainers
		
		$trainer_ids = $dec_utils->getUsersWhereCanCreateFor($this->current_user->getId());

		
		if (count($trainer_ids) > 0) {
			if ($dec_utils->canCreateFor($this->current_user->getId(), $this->current_user->getId())) {
				$trainer_ids = array_merge(array($this->current_user->getId()), $trainer_ids);
			}
			
			$options = gevUserUtils::getFullNames($trainer_ids);

			$trainer_select = new ilMultiSelectInputGUI($this->lng->txt("tutor"), "trainers");
			$trainer_select->setOptions($options);
			$trainer_select->setWidth(250);
			if ($this->user_id !== null) {
				$trainer_select->setValue(array($this->user_id));
			}
			
			$form->addItem($trainer_select);
		}
		else {
			$trainer_hidden =  new ilHiddenInputGUI("trainers[]");
			$trainer_hidden->setValue($this->current_user->getId());
			$form->addItem($trainer_hidden);
		}

		return $form;
	}
	
	protected function buildTrainingOptionsForm($a_fill = false, $a_training_id = null, $a_trainer_ids = null, $a_date = null, $a_template_id = null) {
		require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");
		require_once("Services/Form/classes/class.ilTextAreaInputGUI.php");
		require_once("Services/Form/classes/class.ilTextInputGUI.php");
		require_once("Services/Form/classes/class.ilDateTimeInputGUI.php");
		require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
		require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
		require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		if ($a_training_id === null && $a_template_id === null && $a_fill) {
			throw new Exception("gevDecentralTrainingGUI::buildTrainingOptionsForm: Either set training_id or template_id.");
		}
		
		if ($a_template_id !== null && $a_trainer_ids === null && $a_fill) {
			throw new Exception("gevDecentralTrainingGUI::buildTrainingOptionsForm: You need to set trainer_ids if you set a template_id.");
		}
		
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_settings"));

		if ($a_fill) {
			if ($a_template_id !== null) {
				$training_info = $dec_utils->getTemplateInfoFor($this->current_user_id, $a_template_id);
				$crs_utils = gevCourseUtils::getInstance($a_template_id);
				$tmp = $crs_utils->getSchedule();
				$sched = explode("-",$tmp[0]);
				$trainer_ids = $a_trainer_ids;
				$training_info["date"] = ($a_date !== null) ? new ilDate($a_date, IL_CAL_DATE)
															: new ilDate(date("Y-m-d"), IL_CAL_DATE);
				$training_info["start_datetime"] = new ilDateTime("1970-01-01 ".$sched[0].":00", IL_CAL_DATETIME);
				$training_info["end_datetime"] = new ilDateTime("1970-01-01 ".$sched[1].":00", IL_CAL_DATETIME);
				$training_info["invitation_preview"] = gevCourseUtils::getInstance($a_template_id)->getInvitationMailPreview();
				$training_info["credit_points"] = gevCourseUtils::getInstance($a_template_id)->getCreditPoints();
				$no_changes_allowed = false;
				
				$tmplt_id = new ilHiddenInputGUI("template_id");
				$tmplt_id->setValue($a_template_id);
				$form->addItem($tmplt_id);
				
				$trnrs = new ilHiddenInputGUI("trainer_ids");
				$trnrs->setValue(base64_encode(serialize($a_trainer_ids)));
				$form->addItem($trnrs);
				
			}
			else {
				$crs_utils = gevCourseUtils::getInstance($a_training_id);
				$tmp = $crs_utils->getSchedule();
				$sched = explode("-",$tmp[0]);
				$training_info = array(
					  "title" => $crs_utils->getTitle()
					, "description" => $crs_utils->getSubtitle()
					, "ltype" => $crs_utils->getType()
					, "date" => $crs_utils->getStartDate()
					, "start_datetime" => new ilDateTime("1970-01-01 ".$sched[0].":00", IL_CAL_DATETIME)
					, "end_datetime" => new ilDateTime("1970-01-01 ".$sched[1].":00", IL_CAL_DATETIME)
					, "time" => null
					, "venue" => $crs_utils->getVenueId()
					, "venue_free_text" => $crs_utils->getVenueFreeText()
					, "webinar_link" => $crs_utils->getWebExLink()
					, "webinar_password" => $crs_utils->getWebExPassword()
					, "orgu_id" => $crs_utils->getTEPOrguId()
					, "invitation_preview" => $crs_utils->getInvitationMailPreview()
					, "orgaInfo" => $crs_utils->getOrgaInfo()
					, "credit_points" => $crs_utils->getCreditPoints()
					);
				$trainer_ids = $crs_utils->getTrainers();
				$no_changes_allowed = $crs_utils->isFinalized();
			}
		}
		else {
			$crs_utils = gevCourseUtils::getInstance(intval($_POST["template_id"]));
			$no_changes_allowed = false;
		}
		
		if ($a_training_id !== null) {
			$obj_id = new ilHiddenInputGUI("obj_id");
			$obj_id->setValue($a_training_id);
			$form->addItem($obj_id);
			$crs_utils = gevCourseUtils::getInstance($a_training_id);
			
			if (!$a_fill) {
				$training_info = array(
					  "ltype" => $crs_utils->getType()
					, "title" => $crs_utils->getTitle()
					, "invitation_preview" => $crs_utils->getInvitationMailPreview()
					);
				$no_changes_allowed = $crs_utils->isFinalized();
			}
		}
		
		$title = new ilNonEditableValueGUI($this->lng->txt("title"), "title", false);
		$title->setValue($training_info["title"]);
		$form->addItem($title);
		
		$description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		if ($a_fill) {
			$description->setValue($training_info["description"]);
		}
		$description->setDisabled($no_changes_allowed);
		$form->addItem($description);
		
		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_course_type"), "ltype", false);
		$ltype->setValue($training_info["ltype"]);
		$form->addItem($ltype);
		
		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_credit_points"), "credit_points", false);
		$ltype->setValue($training_info["credit_points"]);
		$form->addItem($ltype);
		
		$date = new ilDateTimeInputGUI($this->lng->txt("date"), "date");
		$date->setShowTime(false);
		if ($a_fill) {
			$date->setDate($training_info["date"]);
		}
		$date->setDisabled($no_changes_allowed);
		$form->addItem($date);
		
		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "time");
		$time->setShowDate(false);
		$time->setShowTime(true);
		if ($a_fill) {
			$time->setStart($training_info["start_datetime"]);
			$time->setEnd($training_info["end_datetime"]);
		}
		$time->setStartText($this->lng->txt("gev_from"));
		$time->setEndText($this->lng->txt("until"));
		$time->setDisabled($no_changes_allowed);
		$form->addItem($time);
		
		$trainers = new ilNonEditableValueGUI($this->lng->txt("tutor"), "tutor", true);
		if ($a_fill) {
			$trainers->setValue(implode("<br />", gevUserUtils::getFullNames($trainer_ids)));
		}
		$trainers->setDisabled($no_changes_allowed);
		$form->addItem($trainers);
		
		if ($crs_utils->isPraesenztraining()) {
			$venue = new ilSelectInputGUI($this->lng->txt("gev_venue"), "venue");
			$venues = array(0 => "-") + gevOrgUnitUtils::getVenueNames();
			$venue->setOptions($venues);
			if ($training_info["venue"] && $a_fill) {
				$venue->setValue($training_info["venue"]);
			}
			$venue->setDisabled($no_changes_allowed);
			$form->addItem($venue);

			$venue_free_text = new ilTextInputGUI($this->lng->txt("gev_venue_free_text"), "venue_free_text");
			if ($training_info["venue_free_text"] && $a_fill) {
				$venue_free_text->setValue($training_info["venue_free_text"]);
			}
			$form->addItem($venue_free_text);
		}
		
		if ($training_info["ltype"] == "Webinar") {
			$webinar_link = new ilTextInputGUI($this->lng->txt("gev_webinar_link"), "webinar_link");
			$webinar_link->setDisabled($no_changes_allowed);
			if ($training_info["webinar_link"] && $a_fill) {
				$webinar_link->setValue($training_info["webinar_link"]);
			}
			$form->addItem($webinar_link);
			
			$webinar_password = new ilTextInputGUI($this->lng->txt("gev_webinar_password"), "webinar_password");
			$webinar_password->setDisabled($no_changes_allowed);
			if ($training_info["webinar_password"] && $a_fill) {
				$webinar_password->setValue($training_info["webinar_password"]);
			}
			$form->addItem($webinar_password);
		}
		
		$orgaInfo = new ilTextAreaInputGUI($this->lng->txt("gev_orga_info"),"orgaInfo");
		if ($training_info["orgaInfo"] && $a_fill) {
				$orgaInfo->setValue($training_info["orgaInfo"]);
			}
		$orgaInfo->setUseRte(true);
		$form->addItem($orgaInfo);

		require_once("Services/TEP/classes/class.ilTEP.php");
		$org_info = ilTEP::getPossibleOrgUnitsForTEPEntriesSeparated();
		require_once "Services/TEP/classes/class.ilTEPOrgUnitSelectionInputGUI.php";
		$orgu_selection = new ilTEPOrgUnitSelectionInputGUI($org_info, "orgu_id", false, false);
		if ($a_fill) {
			$orgu_selection->setValue($training_info["orgu_id"]);
		}
		$orgu_selection->setRecursive(false);
		$form->addItem($orgu_selection);
		
		if ($training_info["invitation_preview"]) {
			$mail_section = new ilFormSectionHeaderGUI();
			$mail_section->setTitle($this->lng->txt("gev_mail_mgmt"));
			$form->addItem($mail_section);

			$this->lng->loadLanguageModule("mail");
			$preview = new ilNonEditableValueGUI($this->lng->txt("gev_preview_invitation_mail"), "", true);
			if ($a_fill) {
				$preview->setValue( "<b>".$this->lng->txt("mail_message_subject")."</b>: ".$training_info["invitation_preview"]["subject"]
								  . "<br /><br />"
								  . $training_info["invitation_preview"]["message_html"]
								  );
			}
			$form->addItem($preview);
		}
		
		return $form;
	}
	
	protected function checkDecentralTrainingConstraints($a_form, $credit_points_source_id) {
		// Check date is before today
		$tmp = $a_form->getInput("date");
		$date = new ilDate($tmp["date"], IL_CAL_DATE);
		$dateUnix = $date->get(IL_CAL_UNIX);
		$now = new ilDate(date('Y-m-d'), IL_CAL_DATE);
		$nowUnix = $now->get(IL_CAL_UNIX);

		if($dateUnix < $nowUnix){
			ilUtil::sendFailure($this->lng->txt("gev_dec_training_date_before_now"), false);
			return false;
		}
		// end check date

		//check venue AND venue freetext is filles
		$venue = $a_form->getInput("venue");
		$venue_free_text = $a_form->getInput("venue_free_text");

		if($venue && $venue_free_text) {
			ilUtil::sendFailure($this->lng->txt("gev_dec_training_two_venues"), false);
			return false;
		}
		// ench check venue

		//check total minutes are to small for credit points
		$tmp = $a_form->getInput("time");
		$start = split(":", $tmp["start"]["time"]);
		$end = split(":", $tmp["end"]["time"]);

		$minutes = 0;
		$hours = 0;
		if($end[1] < $start[1]) {
			$minutes = 60 - $start[1] + $end[1];
			$hours = -1;
		} else {
			$minutes = $end[1] - $start[1];
		}
		$hours = $hours + $end[0] - $start[0];
		$totalMinutes = $hours * 60 + $minutes;

		$crs_utils = gevCourseUtils::getInstance($credit_points_source_id);
		$credit_points = $crs_utils->getCreditPoints();

		if(round(($totalMinutes / 45)) < $credit_points) {
			ilUtil::sendFailure($this->lng->txt("gev_dec_training_crs_to_short"), false);
			return false;
		}
		// end check total time

		return true;
	}
}

?>