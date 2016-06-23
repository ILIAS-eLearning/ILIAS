<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Forms for decentral trainings.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*
* @ilCtrl_Calls gevDecentralTrainingGUI: ilObjCourseGUI
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");
require_once("Services/CaTUIComponents/classes/class.catUploadedFilesGUI.php");

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
require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUVGOrgUnits.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("Services/GEV/Form/classes/class.gevOptgroupSelectInputGUI.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php");
require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");

class gevDecentralTrainingGUI {
	const AUTO_RELOAD_TIMEOUT_MS = 5000;
	const MAX_COURSE_DURATION = 720;
	const VC_TYPE_CSN = "CSN";
	const VC_TYPE_WEBEX = "Webex";
	const LTPYE_WEBINAR = "Webinar";
	const UVG_BASE_ORG_UNIT = "UVG";
	const UPLOAD_ERROR_VALUE = 4;

	protected $ltype;
	
	public function __construct() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog, $ilAccess, $ilToolbar;

		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->log = &$ilLog;
		$this->current_user = &$ilUser;
		$this->toolbar = &$ilToolbar;
		$this->cur_user_utils = gevUserUtils::getInstance($this->current_user->getId());
		$this->access = &$ilAccess;
		$this->user_id = null;
		$this->date = null;
		$this->crs_request_id = null;
		$this->tpl_date_auto_change = null;
		$this->crs_ref_id = null;
		$this->mail_tpl_id = null;
		$this->tmp_path_string = null;
		$this->added_files = null;

		$this->dctl_utils = gevDecentralTrainingUtils::getInstance();
		$this->amd_utils = gevAMDUtils::getInstance();

		$this->tpl->getStandardTemplate();
		$this->tpl->addJavaScript('Services/GEV/DecentralTrainings/js/dct_date_duration_update.js');
		$this->tpl->addJavaScript("Services/GEV/DecentralTrainings/js/dct_disable_mail_preview.js");
		$this->tpl->addJavaScript("Services/CaTUIComponents/js/colorbox-master/jquery.colorbox-min.js");
		$this->tpl->addJavaScript("Services/CaTUIComponents/js/catDeleteUploadedFile.js");

		iljQueryUtil::initjQuery();
	}

	public function executeCommand() {
		$this->loadUserId();
		$this->loadDate();
		$this->loadCrsRequestId();
		$this->loadCrsRefId();
		$this->loadTmpPathString();
		$this->loadAddedFiles();
		
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
			case "addBuildingBlock":
			case "updateBuildingBlock":
			case "showBuildingBlock":
			case "updateCourseData":
			case "saveTrainingSettings":
			case "toChangeCourseData":
			case "forwardCrs":
			case "confirmTrainingCancellation":
			case "cancelTraining":
			case "deliverAttachment":
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

	protected function loadCrsRequestId() {
		if(isset($_GET["crs_request_id"])) {
			$this->crs_request_id = $_GET["crs_request_id"];
		}

		if(isset($_POST["crs_request_id"])) {
			$this->crs_request_id = $_POST["crs_request_id"];
		}

		if($this->crs_request_id == "") { $this->crs_request_id = null; }
	}

	protected function loadCrsRefId() {
		if(isset($_GET["ref_id"])) {
			$this->crs_ref_id = $_GET["ref_id"];
		}
		
		if(isset($_GET["crs_ref_id"])) {
			$this->crs_ref_id = $_GET["crs_ref_id"];
		}
		
		if($this->crs_ref_id == "") {$this->crs_ref_id = null; }
	}

	protected function loadTmpPathString() {
		if(isset($_POST["tmp_path_string"])) {
			$this->tmp_path_string = $_POST["tmp_path_string"];
		}
	}

	protected function loadAddedFiles() {
		if(isset($_POST["added_files"])) {
			$this->added_files = $_POST["added_files"];
		}
	}
	
	protected function cancel() {
		$this->ctrl->redirectByClass("ilTEPGUI");
	}
	
	protected function showOpenRequests() {
		$requests = $this->dctl_utils->getOpenCreationRequests();
		if ($this->dctl_utils->userCanOpenNewCreationRequest() && !$this->dctl_utils->userCanOpenMultipleRequests()) {
			return $this->redirectToBookingFormOfLastCreatedTraining();
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($this->current_user->getId());
		
		$title = new catTitleGUI("gev_dec_training_open_requests_title", "gev_dec_training_open_requests_header_note", "GEV_img/ico-head-create-decentral-training.png");

		$view = $this->dctl_utils->getOpenRequestsView($requests, !$this->dctl_utils->userCanOpenNewCreationRequest());
		
		return  $title->render()
			  . $view;
	}
	
	protected function showOpenRequestsAsNotice() {
		$requests = $this->dctl_utils->getOpenCreationRequests();
		if (count($requests) > 0) {
			$view = $this->dctl_utils->getOpenRequestsView($requests);
			ilUtil::sendInfo($view);
		}
	}
	
	protected function chooseTemplateAndTrainers($a_form = null) {
		if (!$this->dctl_utils->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		$this->showOpenRequestsAsNotice();
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$form = ($a_form === null) ? $this->buildChooseTemplateAndTrainersForm($this->user_id, $this->date)
								   : $a_form;
		
		$tpl = new ilTemplate("tpl.gev_notice.html", false, false, "Services/GEV/DecentralTrainings");
		$tpl->setVariable("NOTICE", $this->lng->txt("gev_dec_training_notice"));
		
		return   $title->render()
				.$form->getHTML()
				.$tpl->get()
				;
	}
	
	protected function createTraining($a_form = null) {
		if (!$this->dctl_utils->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		$this->showOpenRequestsAsNotice();
		
		$form_prev = $this->buildChooseTemplateAndTrainersForm($this->user_id, $this->date);
		$this->dctl_utils = gevDecentralTrainingUtils::getInstance();
		
		if (!$form_prev->checkInput()) {
			return $this->chooseTemplateAndTrainers($form_prev);
		}
		$form_prev->setValuesByPost();
		$selected_tpl = $form_prev->getInput($form_prev->getInput("ltype")."_template");
		
		if($selected_tpl === "-1") {
			$item = $form_prev->getItemByPostVar($form_prev->getInput("ltype")."_template");
			$item->setAlert($this->lng->txt("gev_dec_training_no_tpl_selected"));
			return $this->chooseTemplateAndTrainers($form_prev);
		}


		foreach ($form_prev->getInput("trainers") as $trainer_id) {
			if (!$this->dctl_utils->canCreateFor($this->current_user->getId(), $trainer_id)) {
				throw new Exception( "gevDecentralTrainingGUI::createTraining: No permission for ".$this->current_user->getId()
									." to create training for ".$trainer_id);
			}
		}
		if (count($form_prev->getInput("trainers")) == 0) {
			$form_prev->getItemByPostvar("trainers")->setAlert($this->lng->txt("gev_dec_training_choose_min_one_trainer"));
			return $this->chooseTemplateAndTrainers($form_prev);
		}

		/***********************
		*
		* CREATE FORM FOR TRAINING OPTIONS
		*
		***********************/
		$this->ltype = $form_prev->getInput("ltype");
		$this->template_id = $form_prev->getInput($this->ltype."_template");
		
		require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
		$inv_mail_settings = new gevCrsInvitationMailSettings($this->template_id);
		$this->mail_tpl_id = $inv_mail_settings->getTemplateFor("Mitglied");

		$trainer_ids = $form_prev->getInput("trainers");
		$is_flexible = $this->isTemplateFlexible($this->template_id);
		$fill = true;
		$form_values = $this->getFormValuesByTemplateId($this->template_id, $trainer_ids);
		//utils_id
		$form_values["utils_id"] = $this->template_id;
		//template id
		$form_values["template_id"] = $this->template_id;
		//trainer hinzufügen
		$form_values["trainer_ids"] = $trainer_ids;
		//datum hinzufügen
		$form_values["date"] = ($this->date !== null) ? new ilDate($this->date, IL_CAL_DATE)
															: new ilDate(date("Y-m-d"), IL_CAL_DATE);

		if($is_flexible) {
			$form_values["title"] = "";
		}
		
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		

		$form = ($a_form === null) ? $this->buildTrainingOptionsForm($fill, $is_flexible, $form_values) 
								   : $a_form;
		
		if($is_flexible) {
			$form->addCommandButton("addBuildingBlock", $this->lng->txt("gev_dec_training_add_buildingblocks"));
		} else {
			$form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		}
		$form->addCommandButton("", $this->lng->txt("gev_dec_mail_preview"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$form->setFormAction($this->ctrl->getFormAction($this));


		
		$ret = $title->render()
				.$this->createMailPreview()
				.$form->getHTML();

		if($this->tpl_date_auto_change !== null) {
			$ret .= $this->tpl_date_auto_change->get();
		}

		return $ret;
	}

	protected function showSettingsByRequestId() {
		$fill = true;

		$form_values = $this->getFormValuesByRequestId($this->crs_request_id);
		$this->template_id = $form_values["template_id"];
		$is_flexible = $this->isTemplateFlexible($form_values["template_id"]);
		$crs_utils = gevCourseUtils::getInstance($form_values["template_id"]);
		$form_values["ltype"] = $crs_utils->getType();
		//utils_id
		$form_values["utils_id"] = $form_values["template_id"];
		$this->ltype = $form_values["ltype"];
		$this->webinar_vc_type = $form_values["webinar_vc_type"];

		//for mail preview
		require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
		$inv_mail_settings = new gevCrsInvitationMailSettings($form_values["template_id"]);
		$this->mail_tpl_id = $inv_mail_settings->getTemplateFor("Mitglied");

		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");

		$form = ($a_form === null) ? $this->buildTrainingOptionsForm($fill, $is_flexible, $form_values) 
								   : $a_form;
		
		if($is_flexible) {
			$form->addCommandButton("addBuildingBlock", $this->lng->txt("gev_dec_training_add_buildingblocks"));
		} else {
			$form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		}
		
		$form->addCommandButton("", $this->lng->txt("gev_dec_mail_preview"));
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$form->setFormAction($this->ctrl->getFormAction($this));


		
		$ret = $title->render()
				.$this->createMailPreview()
				.$form->getHTML();

		if($this->tpl_date_auto_change !== null) {
			$ret .= $this->tpl_date_auto_change->get();
		}

		return $ret;
	}

	protected function failCreateTraining($a_form) {
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$tmplt_id = new ilHiddenInputGUI("template_id");
		$a_form->addItem($tmplt_id);
				
		$trnrs = new ilHiddenInputGUI("trainer_ids");
		$a_form->addItem($trnrs);

		$a_form->setValuesByPost();

		$form_tpl_id = $a_form->getInput("template_id");
		$is_flexible = $this->isTemplateFlexible($form_tpl_id);
		
		if($is_flexible) {
			$a_form->addCommandButton("addBuildingBlock", $this->lng->txt("gev_dec_training_add_buildingblocks"));
		} else {
			$a_form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		}

		$a_form->addCommandButton("", $this->lng->txt("gev_dec_mail_preview"));
		$a_form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$a_form->setFormAction($this->ctrl->getFormAction($this));

		if($this->tpl_date_auto_change !== null) {
			$js = $this->tpl_date_auto_change->get();
		}
		else {
			$js = "";
		}

		return   $title->render()
				.$this->createMailPreview()
				.$a_form->getHTML()
				.$js;
	}
	
	protected function finalizeTrainingCreation() {

		if (!$this->dctl_utils->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequest.php");
		
		$this->template_id = intval($_POST["template_id"]);
		$form_values["utils_id"] = $this->template_id;
		
		if(!$this->tmp_path_string) {
			$this->tmp_path_string = $this->randomstring();
		}

		$form_values["tmp_path_string"] = $this->tmp_path_string;

		//ATTACHMENTS
		$form_values["added_files"] = $this->attachmentHandling();

		$form_prev = $this->buildTrainingOptionsForm(false,false,$form_values);
		
		if (!$form_prev->checkInput()) {
			return $this->failCreateTraining($form_prev);
		}
		
		if(!$this->checkDecentralTrainingConstraints($form_prev, $this->template_id)) {
			return $this->failCreateTraining($form_prev);
		}

		$trainer_ids = array_map(function($inp) {return (int)$inp; }
								, explode("|",$form_prev->getInput("trainer_ids"))
								);
		$attachment = $_POST["attachment_upload"];
		
		$crs_utils = gevCourseUtils::getInstance($this->template_id);
		$settings = $this->getSettingsFromForm($crs_utils, $form_prev, $this->template_id);
		$creation_request = new gevDecentralTrainingCreationRequest
								( $this->dctl_utils->getCreationRequestDB()
								, (int)$this->current_user->getId()
								, (int)$this->template_id
								, $trainer_ids
								, $settings
								);
		$creation_request->request();
		$this->dctl_utils->flushOpenCreationRequests();

		ilUtil::sendSuccess($this->lng->txt("gev_dec_training_creation_requested"), true);
		if (!$this->dctl_utils->userCanOpenNewCreationRequest()) {
			$this->ctrl->redirect($this, "showOpenRequests");
		}
		else {
			$this->ctrl->redirectByClass(array("ilTEPGUI"));
		}
	}

	protected function addBuildingBlock() {
		$template_id = $_POST["template_id"];
		$is_flexible = $this->isTemplateFlexible($template_id);
		$form_values["utils_id"] = $template_id;
		$this->template_id = $template_id;

		if(!$this->tmp_path_string) {
			$this->tmp_path_string = $this->randomstring();
		}
		$form_values["tmp_path_string"] = $this->tmp_path_string;
		//ATTACHMENTS
		$form_values["added_files"] = $this->attachmentHandling();

		$form = $this->buildTrainingOptionsForm(false,$is_flexible,$form_values);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->failCreateTraining($form);
		}

		if(!$this->checkDecentralTrainingConstraints($form, $template_id)) {
			return $this->failCreateTraining($form);
		}

		$trainer_ids = array_map(function($inp) {return (int)$inp; }
				, explode("|",$form->getInput("trainer_ids"))
		);


		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequest.php");
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		$crs_utils = gevCourseUtils::getInstance($_POST["template_id"]);
		$settings = $this->getSettingsFromForm($crs_utils, $form, $_POST["template_id"]);

		if($this->crs_request_id === null) {
			$creation_request = new gevDecentralTrainingCreationRequest
												( $dec_utils->getCreationRequestDB()
												, (int)$this->current_user->getId()
												, (int)$_POST["template_id"]
												, $trainer_ids
												, $settings
												);
			$creation_request->save();
		} else {
			require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequestDB.php");
			$request_db = new gevDecentralTrainingCreationRequestDB();
			$creation_request = $request_db->request($this->crs_request_id);
			$creation_request->setSettings($settings);
			$request_db->updateRequest($creation_request);
		}

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$bb_gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI(null, $creation_request->requestId());
		$this->ctrl->forwardCommand($bb_gui);
	}

	protected function updateBuildingBlock() {
		$obj_id = (int)$_POST["obj_id"];
		$form_values["utils_id"] = $obj_id;
		$form_values["obj_id"] = $obj_id;
		$is_flexible = $this->isCrsTemplateFlexible($obj_id);

		try {
			$form_values["added_files"] = $this->handleCustomAttachments();
		} catch (Exception $e) {
			ilUtil::sendInfo($this->lng->txt("gev_dec_training_custom_attachment_exist"),false);
			return $this->showSettings($form);
		}
		
		$form = $this->buildTrainingOptionsForm(false, $is_flexible, $form_values);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->showSettings($form);
		}

		if(!$this->checkDecentralTrainingConstraints($form, $obj_id)) {
			return $this->showSettings($form);
		}

		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
		$dct_utils = gevDecentralTrainingUtils::getInstance();

		$new_vals = array();
		$new_vals["title"] = $_POST["title"];
		$new_vals["desc"] = $_POST["description"] ? $_POST["description"] : null;
		$new_vals["date"] = $_POST["date"]["date"];
		$new_vals["time"] = $_POST["time"];
		$new_vals["orgu_id"] = $_POST["orgu_id"];
		$new_vals["venue_id"] = null;
		$new_vals["venue_free"] = null;
		$new_vals["vc_type"] = null;
		$new_vals["webx_link"] = null;
		$new_vals["webx_password"] = null;

		if(isset($_POST["venue"])) {
			$new_vals["venue_id"] = $_POST["venue"];
			$new_vals["venue_free"] = $_POST["venue_free_text"] ? $_POST["venue_free_text"] : null;
		}

		if(isset($_POST["webinar_vc_type"])){
			$new_vals["vc_type"] = ($_POST["webinar_vc_type"] != "0") ? $_POST["webinar_vc_type"] : null;
			$new_vals["webx_link"] = $_POST["webinar_link"] ? $_POST["webinar_link"] : null;
			$new_vals["webx_password"] = $_POST["webinar_password"]? $_POST["webinar_password"] : null;
		}

		$resend_mail = $dct_utils->isResendMailRequired($obj_id, $new_vals);

		if($resend_mail) {
			require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
			$crs_mails = new gevCrsAutoMails($obj_id);
			$crs_mails->sendDeferred("invitation");
		}

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_utils = gevCourseUtils::getInstance($obj_id);
		$tmpl_id = gevObjectUtils::getObjId($crs_utils->getTemplateRefId());

		if($crs_utils->userHasPermissionTo($this->current_user->getId(),"change_trainer")) {
			$trainer_ids_new = $form->getInput("tutor_change");
			$trainer_ids_old = explode("|",$form->getInput("trainer_ids"));
			$this->updateTrainers($trainer_ids_new,$trainer_ids_old,$crs_utils);
		}

		$settings = $this->getSettingsFromForm($crs_utils, $form, $tmpl_id);
		$settings->applyTo((int)$_POST["obj_id"]);

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$bb_gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($_POST["obj_id"]);
		$this->ctrl->forwardCommand($bb_gui);
	}

	protected function updateTrainers($trainer_ids_new, $trainer_ids_old, $crs_utils) {
		$delete = array_diff($trainer_ids_old, $trainer_ids_new);
		$add = array_diff($trainer_ids_new, $trainer_ids_old);

		$defaultTutorRole = $crs_utils->getCourse()->getDefaultTutorRole();
		foreach ($add as $value) {
			gevRoleUtils::getRbacAdmin()->assignUser($defaultTutorRole, $value);
		}

		$crs_utils->cancelTrainer($delete);
	}
	
	protected function redirectToBookingFormOfLastCreatedTraining() {
		$obj_id = $this->dctl_utils->lastCreatedCourseId();
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
		
			if ($this->crs_ref_id == null) {
				throw new Exception("gevDecentralTrainingGUI::modifySettings: no ref id");
			}

			$obj_id = gevObjectUtils::getObjId($this->crs_ref_id);
			$form_values = $this->getFormValuesByCrsObjId($obj_id);
			$form_values["utils_id"] = $obj_id;
			$form_values["obj_id"] = $obj_id;
			$is_flexible = $this->isCrsTemplateFlexible($obj_id);
			$this->template_id = $obj_id;
			$this->ltype = $form_values["ltype"];
			$this->webinar_vc_type = $form_values["webinar_vc_type"];

			//for mail preview
			require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
			$inv_mail_settings = new gevCrsInvitationMailSettings($obj_id);
			$this->mail_tpl_id = $inv_mail_settings->getTemplateFor("Mitglied");

			$form = $this->buildTrainingOptionsForm(true, $is_flexible, $form_values);
			$is_started = $form_values["no_changes_allowed"];
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			$crs_utils = gevCourseUtils::getInstance($obj_id);
		}
		else {
			$form = $a_form;
			$obj_id = intval($_POST["obj_id"]);
			$is_flexible = $this->isCrsTemplateFlexible($obj_id);
			$this->template_id = $obj_id;
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			$crs_utils = gevCourseUtils::getInstance($obj_id);
			$is_started = $crs_utils->isStarted();
			require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
			$inv_mail_settings = new gevCrsInvitationMailSettings($obj_id);
			$this->mail_tpl_id = $inv_mail_settings->getTemplateFor("Mitglied");
		}

		$title = new catTitleGUI("gev_dec_training_settings_header"
								, "gev_dec_training_settings_header_note"
								, "GEV_img/ico-head-create-decentral-training.png"
								);

		if($crs_utils->userCanCancelCourse($this->current_user->getId())) {
			$form->addCommandButton("confirmTrainingCancellation", $this->lng->txt("cancel_training"));
		}

		$form->addCommandButton("forwardCrs", $this->lng->txt("gev_dec_forward_to_kurs"));
		if($is_flexible) {
			if(!$is_started) {
				$this->ctrl->setParameter($this,"ref_id", $this->crs_ref_id);
				$form->addCommandButton("updateCourseData", $this->lng->txt("save"));
				$form->addCommandButton("updateBuildingBlock", $this->lng->txt("gev_dec_training_update_buildingblocks"));
			} else {
				$form->addCommandButton("showBuildingBlock", $this->lng->txt("gev_dec_training_show_buildingblocks"));
			}
		} else {
			if(!$is_started) {
				$form->addCommandButton("updateSettings", $this->lng->txt("save"));
			}
		}
		
		$form->addCommandButton("", $this->lng->txt("gev_dec_mail_preview"));
		$form->addCommandButton("cancel", $this->lng->txt("back"));
		$form->setFormAction($this->ctrl->getFormAction($this));
				
		
		
	
		$ret = $title->render()
				.$this->createMailPreview()
				.$form->getHTML();

		if($this->tpl_date_auto_change !== null) {
			$ret .= $this->tpl_date_auto_change->get();
		}

		$this->ctrl->clearParameters($this);

		return $ret;
	}
	
	protected function updateSettings() {
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		$obj_id = $_POST["obj_id"];
		$form_values["utils_id"] = $obj_id;
		$this->crs_ref_id = gevObjectUtils::getRefId($obj_id);
		$is_flexible = $this->isCrsTemplateFlexible($obj_id);

		try {
			$form_values["added_files"] = $this->handleCustomAttachments();
		} catch (Exception $e) {
			ilUtil::sendInfo($this->lng->txt("gev_dec_training_custom_attachment_exist"),false);
			return $this->showSettings($form);
		}

		$form = $this->buildTrainingOptionsForm(false, $is_flexible, $form_values);

		//gev patch start
		$form->setValuesByPost();
		$element = $form->getItemByPostvar("correct_data");
		$element->setChecked(false);

		if (!$form->checkInput()) {
			return $this->showSettings($form);
		}
		
		$crs_utils = gevCourseUtils::getInstance($_POST["obj_id"]);
		$template_ref_id = $crs_utils->getTemplateRefId();
		$tmpl_id = gevObjectUtils::getObjId($template_ref_id);

		if(!$this->checkDecentralTrainingConstraints($form, $tmpl_id)) {
			return $this->showSettings($form);
		}
		
		if (!$this->access->checkAccess("write_reduced_settings", "", gevObjectUtils::getRefId($_POST["obj_id"]))) {
			$this->log->write("gevDecentralTrainingGUI::updateSettings: User ".$this->current_user->getId()
							 ." tried to update Settings but has no permission.");
			throw new Exception("gevDecentralTrainingGUI::updateSettings: no permission");
		}

		if($crs_utils->userHasPermissionTo($this->current_user->getId(),"change_trainer")) {
			$trainer_ids_new = $form->getInput("tutor_change");
			$trainer_ids_old = explode("|",$form->getInput("trainer_ids"));
			$this->updateTrainers($trainer_ids_new,$trainer_ids_old,$crs_utils);
		}
		
		$settings = $this->getSettingsFromForm($crs_utils, $form, $tmpl_id);
		$settings->applyTo((int)$_POST["obj_id"]);
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
		return $this->showSettings($form);
	}
	
	protected function getSettingsFromForm(gevCourseUtils $a_target, $a_form, $a_template_id) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingSettings.php");
		require_once("Services/Calendar/classes/class.ilDateTime.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");

		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();

		$is_flexible = false;
		if($a_template_id == $presence_flexible_tpl_id || $a_template_id == $webinar_flexible_tpl_id) {
			$is_flexible = true;
		}
		
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

			if($is_flexible){
				$vc_type = $a_form->getInput("webinar_vc_type");
			} else {
				$vc_type = null;
			}
		}
		else {
			$webinar_link = null;
			$webinar_password = null;
			$vc_type = null;
		}
		
		$orgu_id = $a_form->getInput("orgu_id");
		$description = $a_form->getInput("description");
		$orgaInfo = $a_form->getInput("orgaInfo");

		$title = $a_form->getInput("title");
		$training_category = null;
		$target_group = null;
		$gdv_topic = null;
		$tmp_path_string = $this->tmp_path_string;
		$uploaded_files = ($this->added_files === null) ? array() : $this->added_files;

		//GDV_TOPIC und TRAINING_CATEGORY JUST DISABLED
		if($is_flexible) {
			$title = $a_form->getInput("title");
			$tg = $a_form->getInput("target_groups");
			$target_group = $tg ? $tg : array();
			$gdv_topic_temp = $a_form->getInput("gdv_topic");

			//$tc = $a_form->getInput("training_category");
			$training_category = null;//$tc ? $tc : array();
			$gdv_topic = null; //($gdv_topic_temp != "0") ? $gdv_topic_temp : null;
		}

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
						, $title
						, $vc_type
						, $training_category
						, $target_group
						, $gdv_topic
						, $tmp_path_string
						, $uploaded_files
						);
	}
	
	protected function buildChooseTemplateAndTrainersForm($a_user_id = null, $a_date = null) {
		require_once("Services/Form/classes/class.ilRadioGroupInputGUI.php");
		require_once("Services/Form/classes/class.ilRadioOption.php");
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
		require_once("Services/Form/classes/class.ilHiddenInputGUI.php");

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
		$_templates = $this->dctl_utils->getAvailableTemplatesFor($this->current_user->getId());
		
		/*if (count($_templates) == 0) {
			ilUtil::sendFailure($this->lng->txt("gev_dec_training_no_templates"), true);
			$this->ctrl->redirectByClass("ilTEPGUI");
		}*/
		
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
			$templates[$info["ltype"]][$info["group"]][$info["obj_id"]] = $info["title"];
		}

		$ltype_choice = new ilRadioGroupInputGUI($this->lng->txt("gev_course_type"), "ltype");
		$form->addItem($ltype_choice);
		$selected = "";

		foreach ($templates as $ltype => $groups) {
			$desc = "";
			if($ltype == "Präsenztraining") {
				$desc = $this->lng->txt("gev_dec_training_presence_fix");
				$selected = "präsenztraining";
			}

			if($ltype == "Webinar") {
				$desc = $this->lng->txt("gev_dec_training_webinar_fix");
				
				if($selected != "präsenztraining") {
					$selected = "webinar";
				}
			}

			$key = strtolower(str_replace(" ", "_", $ltype));
			$ltype_opt = new ilRadioOption($desc, $key);
			$ltype_choice->addOption($ltype_opt);
			
			$training_select = new gevOptgroupSelectInputGUI($this->lng->txt("gev_dec_training_template"), $key."_template");
			$training_select->setOptions($groups);

			$ltype_opt->addSubItem($training_select);
		}
		
		//load templates id's for flexible trainings
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();

		//Präsenztraining Flexsibel
		if($presence_flexible_tpl_id 
			&& $this->dctl_utils->canUseTemplate(gevObjectUtils::getRefId($presence_flexible_tpl_id), $this->current_user->getId())) 
		{
			if($selected == "") {
				$selected = "presence_flexible";
			}

			$presence_flexible = new ilRadioOption($this->lng->txt("gev_dec_training_presence_flex"), "presence_flexible");
			$ltype_choice->addOption($presence_flexible);
		
			$presence_flexible_tpl_id_hidden = new ilHiddenInputGUI("presence_flexible_template");
			$presence_flexible_tpl_id_hidden->setValue($presence_flexible_tpl_id);
			$form->addItem($presence_flexible_tpl_id_hidden);
		}

		//Webinar Flexsibel
		if($webinar_flexible_tpl_id 
			&& $this->dctl_utils->canUseTemplate(gevObjectUtils::getRefId($webinar_flexible_tpl_id), $this->current_user->getId())) 
		{
			if($selected == "") {
				$selected = "webinar_flexible";
			}

			$webinar_flexible = new ilRadioOption($this->lng->txt("gev_dec_training_webinar_flex"), "webinar_flexible");
			$ltype_choice->addOption($webinar_flexible);
		
			$webinar_flexible_tpl_id_hidden = new ilHiddenInputGUI("webinar_flexible_template");
			$webinar_flexible_tpl_id_hidden->setValue($webinar_flexible_tpl_id);
			$form->addItem($webinar_flexible_tpl_id_hidden);
		}

		$ltype_choice->setValue($selected);
		
		
		// maybe choice of trainers
		
		$trainer_ids = $this->dctl_utils->getUsersWhereCanCreateFor($this->current_user->getId());
		
		if (count($trainer_ids) > 0) {
			if ($this->dctl_utils->canCreateFor($this->current_user->getId(), $this->current_user->getId())) {
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
	
	protected function buildTrainingOptionsForm($a_fill, $a_is_flexible = null, array $a_form_values = null) {
		if($a_is_flexible) {
			return $this->buildTrainingOptionsFormFlexible($a_fill, $a_form_values);
		}

		return $this->buildTrainingOptionsFormStable($a_fill, $a_form_values);
	}

	protected function buildTrainingOptionsFormStable($a_fill, array $a_form_values = null) {
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_settings"));

		$crs_utils = gevCourseUtils::getInstance(intval($a_form_values["utils_id"]));

		/*************************
		* HIDDEN
		*************************/
		$tmplt_id = new ilHiddenInputGUI("template_id");
		$tmplt_id->setValue($a_form_values["template_id"]);
		$form->addItem($tmplt_id);

		$obj_id = new ilHiddenInputGUI("obj_id");
		$obj_id->setValue($a_form_values["obj_id"]);
		$form->addItem($obj_id);
		
		$trnrs = new ilHiddenInputGUI("trainer_ids");
		if($a_form_values["trainer_ids"]) {
			$trnrs->setValue(implode("|",$a_form_values["trainer_ids"]));
		}		
		$form->addItem($trnrs);

		/*************************
		* TITEL
		*************************/
		$title_section = new ilFormSectionHeaderGUI();
		$title_section->setTitle($this->lng->txt("gev_dec_training_title"));
		$form->addItem($title_section);

		$title = new ilNonEditableValueGUI($this->lng->txt("title"), "title", false);
		$title->setValue($a_form_values["title"]);
		$form->addItem($title);
		
		$description = new ilTextAreaInputGUI($this->lng->txt("crs_description"), "description");
		if ($a_fill) {
			$description->setValue($a_form_values["description"]);
		}
		$description->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($description);
		
		/*************************
		* ZEITRAUM
		*************************/
		$duration_section = new ilFormSectionHeaderGUI();
		$duration_section->setTitle($this->lng->txt("gev_dec_training_duration"));
		$form->addItem($duration_section);
		
		$date = new ilDateTimeInputGUI($this->lng->txt("date"), "date");
		$date->setShowTime(false);
		if ($a_fill) {
			$date->setDate($a_form_values["date"]);
		}
		$date->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($date);

		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "time");
		$time->setShowDate(false);
		$time->setShowTime(true);
		if ($a_fill) {
			$time->setStart($a_form_values["start_datetime"]);
			$time->setEnd($a_form_values["end_datetime"]);
		}
		$time->setStartText($this->lng->txt("gev_from"));
		$time->setEndText($this->lng->txt("until"));
		$time->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($time);
		
		if($this->crs_ref_id !== null && $crs_utils->userHasPermissionTo($this->current_user->getId(),"change_trainer")) {
			$trainer_ids = $this->dctl_utils->getUsersWhereCanCreateFor($this->current_user->getId());
			
			if ($this->dctl_utils->canCreateFor($this->current_user->getId(), $this->current_user->getId())) {
				$trainer_ids = array_merge(array($this->current_user->getId()), $trainer_ids);
			}

			if(isset($a_form_values["trainer_ids"]) && is_array($a_form_values["trainer_ids"])) {
				$trainer_ids = array_merge($a_form_values["trainer_ids"], $trainer_ids);
			}
			
			$options = gevUserUtils::getFullNames($trainer_ids);

			$trainer_select = new ilMultiSelectInputGUI($this->lng->txt("tutor"), "tutor_change");
			$trainer_select->setOptions($options);
			$trainer_select->setWidth(250);
			$trainer_select->setValue($a_form_values["trainer_ids"]);

			$now = date("Y-m-d");
			$trainer_select->setDisabled(($now > $crs_utils->getStartDate()->get(IL_CAL_DATE)));

			$trainer_select->setRequired(true);
			
			$form->addItem($trainer_select);
		} else {
			$trainers = new ilNonEditableValueGUI($this->lng->txt("tutor"), "tutor", true);
			if ($a_fill) {
				$trainers->setValue(implode("<br />", gevUserUtils::getFullNames($a_form_values["trainer_ids"])));
			}
			$trainers->setDisabled($a_form_values["no_changes_allowed"]);
			$form->addItem($trainers);
		}
		
		/*************************
		* ORT UND ANBIETER
		*************************/
		$venue_section = new ilFormSectionHeaderGUI();
		$venue_section->setTitle($this->lng->txt("gev_dec_training_venue"));
		$form->addItem($venue_section);

		if ($crs_utils->isPraesenztraining()) {
			$venue = new ilSelectInputGUI($this->lng->txt("gev_venue"), "venue");
			$venues = array(0 => "-") + gevOrgUnitUtils::getVenueNames();
			$venue->setOptions($venues);
			if ($a_form_values["venue"] && $a_fill) {
				$venue->setValue($a_form_values["venue"]);
			}
			$venue->setDisabled($a_form_values["no_changes_allowed"]);
			$form->addItem($venue);

			$venue_free_text = new ilTextInputGUI($this->lng->txt("gev_venue_free_text"), "venue_free_text");
			$venue_free_text->setInfo($this->lng->txt("gev_dec_training_venue_free_text_info"));
			$venue_free_text->setDisabled($a_form_values["no_changes_allowed"]);
			
			if ($a_form_values["venue_free_text"] && $a_fill) {
				$venue_free_text->setValue($a_form_values["venue_free_text"]);
			}
			$form->addItem($venue_free_text);
		}
		require_once("Services/TEP/classes/class.ilTEP.php");
		$org_info = ilTEP::getPossibleOrgUnitsForDecentralTrainingEntriesSeparated();
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingOrgUnitSelectionInputGUI.php");
		$orgu_selection = new gevDecentralTrainingOrgUnitSelectionInputGUI($org_info, "orgu_id", false, false);
		if ($a_fill) {
			$orgu_selection->setValue($a_form_values["orgu_id"]);
		}
		$orgu_selection->setRecursive(false);
		$orgu_selection->setRequired(true);
		$orgu_selection->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($orgu_selection);

		/*************************
		* ORGANISTION
		*************************/
		$orga_section = new ilFormSectionHeaderGUI();
		$orga_section->setTitle($this->lng->txt("gev_dec_training_orga"));
		$form->addItem($orga_section);

		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_course_type"), "ltype", false);
		$ltype->setValue($a_form_values["ltype"]);
		$form->addItem($ltype);

		if ($crs_utils->isWebinar()) {
			$webinar_link = new ilTextInputGUI($this->lng->txt("gev_webinar_link"), "webinar_link");
			$webinar_link->setDisabled($a_form_values["no_changes_allowed"]);
			if ($a_form_values["webinar_link"] && $a_fill) {
				$webinar_link->setValue($a_form_values["webinar_link"]);
			}
			$form->addItem($webinar_link);
			
			$webinar_password = new ilTextInputGUI($this->lng->txt("gev_webinar_password"), "webinar_password");
			$webinar_password->setDisabled($a_form_values["no_changes_allowed"]);
			if ($a_form_values["webinar_password"] && $a_fill) {
				$webinar_password->setValue($a_form_values["webinar_password"]);
			}
			$form->addItem($webinar_password);
		}
		
		$orgaInfo = new ilTextAreaInputGUI($this->lng->txt("gev_orga_info"),"orgaInfo");
		$orgaInfo->setDisabled($a_form_values["no_changes_allowed"]);
		if ($a_fill) {
			$orgaInfo->setValue(($a_form_values["orgaInfo"]===null)? " " : $a_form_values["orgaInfo"]);
		}
		$orgaInfo->setUseRte(true);
		$form->addItem($orgaInfo);

		/*************************
		* ANHANG
		*************************/
		$orga_section = new ilFormSectionHeaderGUI();
		$orga_section->setTitle($this->lng->txt("gev_dec_training_attachment"));
		$form->addItem($orga_section);
		$form->addItem($this->createAttachmentUploadForm($a_form_values["no_changes_allowed"]));

		if($a_form_values["added_files"]) {
			foreach ($a_form_values["added_files"] as $key => $value) {
				$form->addItem($this->addUploadedFileGUI($key, $value, $a_form_values["no_changes_allowed"]));
			}
		}

		if($a_form_values["tmp_path_string"]) {
			$path_hidden = new ilHiddenInputGUI("tmp_path_string");
			$path_hidden->setValue($a_form_values["tmp_path_string"]);
			$form->addItem($path_hidden);
		}

		/*************************
		* ABFRAGE
		*************************/
		$correct_data = new ilCheckboxInputGUI($this->lng->txt("gev_dec_training_correct_data_confirm"),"correct_data");
		$correct_data->setOptionTitle($this->lng->txt("gev_dec_training_correct_data_text"));
		$form->addItem($correct_data);

		/*************************
		* DATUM SPERRE
		*************************/
		if($crs_utils !== null) {
			$credit_points = $crs_utils->getCreditPoints();
			if($credit_points !== null && $credit_points > 0) {
				$this->tpl_date_auto_change = new ilTemplate("tpl.gev_dct_duration_update_js.html", false, false, "Services/GEV/DecentralTrainings");
			}
		}
		
		return $form;
	}

	protected function buildTrainingOptionsFormFlexible($a_fill, array $a_form_values = null) {
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_settings"));
		
		$crs_utils = gevCourseUtils::getInstance(intval($a_form_values["utils_id"]));

		/*************************
		* HIDDEN
		*************************/
		$tmplt_id = new ilHiddenInputGUI("template_id");
		if($a_form_values["template_id"] && $a_fill) {
			$tmplt_id->setValue($a_form_values["template_id"]);
		}
		$form->addItem($tmplt_id);

		$obj_id = new ilHiddenInputGUI("obj_id");
		if($a_form_values["obj_id"] && $a_fill) {
			$obj_id->setValue($a_form_values["obj_id"]);
		}
		$form->addItem($obj_id);
		
		$trnrs = new ilHiddenInputGUI("trainer_ids");
		if($a_form_values["trainer_ids"] && $a_fill) {
			$trnrs->setValue(implode("|",$a_form_values["trainer_ids"]));
		}
		$form->addItem($trnrs);

		$crs_request_id = new ilHiddenInputGUI("crs_request_id");
		$crs_request_id->setValue($this->crs_request_id);
		$form->addItem($crs_request_id);
		
		/*************************
		* TITEL
		*************************/
		$title_section = new ilFormSectionHeaderGUI();
		$title_section->setTitle($this->lng->txt("gev_dec_training_title"));
		$form->addItem($title_section);

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		$title->setDisabled($a_form_values["no_changes_allowed"]);
		if($a_form_values["title"] && $a_fill) {
			$title->setValue($a_form_values["title"]);
		}
		$title->setMaxLength(100);
		$title->setRequired(true);
		$form->addItem($title);
		
		$description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		if ($a_fill) {
			$description->setValue($a_form_values["description"]);
		}
		$description->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($description);
		
		/*************************
		* ZEITRAUM
		*************************/
		$duration_section = new ilFormSectionHeaderGUI();
		$duration_section->setTitle($this->lng->txt("gev_dec_training_duration"));
		$form->addItem($duration_section);

		$date = new ilDateTimeInputGUI($this->lng->txt("date"), "date");
		$date->setShowTime(false);
		if ($a_fill) {
			$date->setDate($a_form_values["date"]);
		}
		$date->setRequired(true);
		$date->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($date);

		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "time");
		$time->setShowDate(false);
		$time->setShowTime(true);
		if ($a_fill) {
			$time->setStart($a_form_values["start_datetime"]);
			$time->setEnd($a_form_values["end_datetime"]);
		}
		$time->setRequired(true);
		$time->setStartText($this->lng->txt("gev_from"));
		$time->setEndText($this->lng->txt("until"));
		$time->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($time);
		
		if($this->crs_ref_id !== null && $crs_utils->userHasPermissionTo($this->current_user->getId(),"change_trainer")) {
			$trainer_ids = $this->dctl_utils->getUsersWhereCanCreateFor($this->current_user->getId());
			
			if ($this->dctl_utils->canCreateFor($this->current_user->getId(), $this->current_user->getId())) {
				$trainer_ids = array_merge(array($this->current_user->getId()), $trainer_ids);
			}

			if(isset($a_form_values["trainer_ids"]) && is_array($a_form_values["trainer_ids"])) {
				$trainer_ids = array_merge($a_form_values["trainer_ids"], $trainer_ids);
			}

			$options = gevUserUtils::getFullNames($trainer_ids);

			$trainer_select = new ilMultiSelectInputGUI($this->lng->txt("tutor"), "tutor_change");
			$trainer_select->setOptions($options);
			$trainer_select->setWidth(250);
			$trainer_select->setValue($a_form_values["trainer_ids"]);
			
			$now = date("Y-m-d");
			$trainer_select->setDisabled(($now > $crs_utils->getStartDate()->get(IL_CAL_DATE)));

			$trainer_select->setRequired(true);

			$form->addItem($trainer_select);
		} else {
			$trainers = new ilNonEditableValueGUI($this->lng->txt("tutor"), "tutor", true);
			if ($a_fill) {
				$trainers->setValue(implode("<br />", gevUserUtils::getFullNames($a_form_values["trainer_ids"])));
			}
			$trainers->setDisabled($a_form_values["no_changes_allowed"]);
			$form->addItem($trainers);
		}
		
		/*************************
		* ORT UND ANBIETER
		*************************/
		$venue_section = new ilFormSectionHeaderGUI();
		$venue_section->setTitle($this->lng->txt("gev_dec_training_venue"));
		$form->addItem($venue_section);

		if ($crs_utils->isPraesenztraining()) {
			$venue = new ilSelectInputGUI($this->lng->txt("gev_venue"), "venue");
			$venues = array(0 => "-") + gevOrgUnitUtils::getVenueNames();
			$venue->setOptions($venues);
			if ($a_form_values["venue"] && $a_fill) {
				$venue->setValue($a_form_values["venue"]);
			}
			$venue->setDisabled($a_form_values["no_changes_allowed"]);
			$form->addItem($venue);

			$venue_free_text = new ilTextInputGUI($this->lng->txt("gev_venue_free_text"), "venue_free_text");
			$venue_free_text->setInfo($this->lng->txt("gev_dec_training_venue_free_text_info"));
			$venue_free_text->setDisabled($a_form_values["no_changes_allowed"]);
			if ($a_form_values["venue_free_text"] && $a_fill) {
				$venue_free_text->setValue($a_form_values["venue_free_text"]);
			}
			$form->addItem($venue_free_text);
		}
		require_once("Services/TEP/classes/class.ilTEP.php");
		
		$org_info = ilTEP::getPossibleOrgUnitsForDecentralTrainingEntriesSeparated();
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingOrgUnitSelectionInputGUI.php");
		$orgu_selection = new gevDecentralTrainingOrgUnitSelectionInputGUI($org_info, "orgu_id", false, false);
		if ($a_fill) {
			$orgu_selection->setValue($a_form_values["orgu_id"]);
		}
		$orgu_selection->setRecursive(false);
		$orgu_selection->setRequired(true);
		$orgu_selection->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($orgu_selection);
		
		
		/*************************
		* ORGANISTION
		*************************/
		$orga_section = new ilFormSectionHeaderGUI();
		$orga_section->setTitle($this->lng->txt("gev_dec_training_orga"));
		$form->addItem($orga_section);

		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_course_type"), "ltype", false);
		$ltype->setValue($a_form_values["ltype"]);
		$form->addItem($ltype);

		if ($crs_utils->isWebinar()) {
			$vc_type_options = array("CSN"=>"CSN","Webex"=>"Webex");
			$webinar_vc_type = new ilSelectInputGUI($this->lng->txt("gev_dec_training_vc_type"),"webinar_vc_type");
			$webinar_vc_type->setDisabled($a_form_values["no_changes_allowed"]);
			$options = array(0 => "-") + $vc_type_options;
			$webinar_vc_type->setOptions($options);
			if($a_form_values["webinar_vc_type"] && $a_fill){
				$webinar_vc_type->setValue($a_form_values["webinar_vc_type"]);
			}
			$form->addItem($webinar_vc_type);

			$webinar_link = new ilTextInputGUI($this->lng->txt("gev_webinar_link"), "webinar_link");
			$webinar_link->setDisabled($a_form_values["no_changes_allowed"]);
			$webinar_link->setRequired(true);
			if ($a_form_values["webinar_link"] && $a_fill) {
				$webinar_link->setValue($a_form_values["webinar_link"]);
			}
			$form->addItem($webinar_link);
			
			$webinar_password = new ilTextInputGUI($this->lng->txt("gev_webinar_password"), "webinar_password");
			$webinar_password->setDisabled($a_form_values["no_changes_allowed"]);
			if ($a_form_values["webinar_password"] && $a_fill) {
				$webinar_password->setValue($a_form_values["webinar_password"]);
			}
			$form->addItem($webinar_password);
		}

		//organisatorisches
		$orgaInfo = new ilTextAreaInputGUI($this->lng->txt("gev_orga_info"),"orgaInfo");
		if ($a_fill) {
			$orgaInfo->setValue(($a_form_values["orgaInfo"]===null)? " " : $a_form_values["orgaInfo"]);
		}
		$orgaInfo->setUseRte(true);
		$orgaInfo->setDisabled($a_form_values["no_changes_allowed"]);
		$form->addItem($orgaInfo);
		
		/*************************
		* INHALT
		*************************/
		$content_section = new ilFormSectionHeaderGUI();
		$content_section->setTitle($this->lng->txt("gev_dec_training_content"));
		$form->addItem($content_section);

		//zielgruppe
		$target_groups = $this->amd_utils->getOptions(gevSettings::CRS_AMD_TARGET_GROUP);
		$cbx_group_target_groups = new ilCheckBoxGroupInputGUI($this->lng->txt("gev_dec_training_target_groups"),"target_groups");
		$cbx_group_target_groups->setDisabled($a_form_values["no_changes_allowed"]);

		foreach($target_groups as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			$cbx_group_target_groups->addOption($option);
		}

		if($a_form_values["target_groups"] && $a_fill){
			$cbx_group_target_groups->setValue($a_form_values["target_groups"]);
		} else {
			$cbx_group_target_groups->setValue(array("Unabhängige Vertriebspartner"));
		}
		$form->addItem($cbx_group_target_groups);

		/*************************
		* ANHANG
		*************************/
		$orga_section = new ilFormSectionHeaderGUI();
		$orga_section->setTitle($this->lng->txt("gev_dec_training_attachment"));
		$form->addItem($orga_section);
		$form->addItem($this->createAttachmentUploadForm($a_form_values["no_changes_allowed"]));

		if($a_form_values["added_files"]) {
			foreach ($a_form_values["added_files"] as $key => $value) {
				$form->addItem($this->addUploadedFileGUI($key,$value, $a_form_values["no_changes_allowed"]));
			}
		}

		if($a_form_values["tmp_path_string"]) {
			$path_hidden = new ilHiddenInputGUI("tmp_path_string");
			$path_hidden->setValue($a_form_values["tmp_path_string"]);
			$form->addItem($path_hidden);
		}

		/*************************
		* ABFRAGE
		*************************/
		$correct_data = new ilCheckboxInputGUI($this->lng->txt("gev_dec_training_correct_data_confirm"),"correct_data");
		$correct_data->setOptionTitle($this->lng->txt("gev_dec_training_correct_data_text"));
		$form->addItem($correct_data);

		return $form;
	}
	
	protected function checkDecentralTrainingConstraints(&$a_form, $a_template_id) {
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
		$crs_utils = gevCourseUtils::getInstance($a_template_id);
		
		//check venue AND venue freetext is filles
		if($crs_utils->isPraesenztraining()) {
			$venue = $a_form->getInput("venue");
			$venue_free_text = $a_form->getInput("venue_free_text");

			if($venue && $venue_free_text) {
				ilUtil::sendFailure($this->lng->txt("gev_dec_training_two_venues"), false);
				return false;
			}

			if(!$venue && !$venue_free_text) {
				ilUtil::sendFailure($this->lng->txt("gev_dec_training_no_venue"), false);
				$item = $a_form->getItemByPostVar("venue");
				$item->setAlert($this->lng->txt("gev_dec_training_no_venue_error_msg"));
				return false;
			}
		}
		// ench check venue
		$confirmed = $a_form->getInput("correct_data");
		if($confirmed === "") {
			ilUtil::sendFailure($this->lng->txt("gev_dec_training_correct_data_no_confirm"), false);
			$item = $a_form->getItemByPostVar("correct_data");
			$item->setAlert($this->lng->txt("gev_dec_training_correct_data_no_confirm"));
			return false;
		}

		//check total minutes are to small for credit points
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();
		
		if($a_template_id == $presence_flexible_tpl_id || $a_template_id == $webinar_flexible_tpl_id) {
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
			
			if($totalMinutes > self::MAX_COURSE_DURATION) {
				ilUtil::sendFailure($this->lng->txt("gev_dec_training_crs_to_long"), false);
				return false;
			}
		}
		// end check total time

		return true;
	}

	protected function showBuildingBlock() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlock2GUI.php");
		$bb_gui = new gevDecentralTrainingCourseCreatingBuildingBlock2GUI($_POST["obj_id"],null,true);
		$this->ctrl->forwardCommand($bb_gui);
	}

	function updateCourseData() {
		$obj_id = $_POST["obj_id"];
		$form_values["utils_id"] = $obj_id;
		$form_values["obj_id"] = $obj_id;
		$is_flexible = $this->isCrsTemplateFlexible($obj_id);

		try {
			$form_values["added_files"] = $this->handleCustomAttachments();
		} catch (Exception $e) {
			ilUtil::sendInfo($this->lng->txt("gev_dec_training_custom_attachment_exist"),false);
			return $this->showSettings($form);
		}

		$form = $this->buildTrainingOptionsForm(false, $is_flexible, $form_values);

		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->showSettings($form);
		}
		
		if(!$this->checkDecentralTrainingConstraints($form, $obj_id)) {
			return $this->showSettings($form);
		}

		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_utils = gevCourseUtils::getInstance($obj_id);
		$tmpl_id = gevObjectUtils::getObjId($crs_utils->getTemplateRefId());

		if($crs_utils->userHasPermissionTo($this->current_user->getId(),"change_trainer")) {
			$trainer_ids_new = $form->getInput("tutor_change");
			$trainer_ids_old = explode("|",$form->getInput("trainer_ids"));
			$this->updateTrainers($trainer_ids_new,$trainer_ids_old,$crs_utils);
		}

		$settings = $this->getSettingsFromForm($crs_utils, $form, $tmpl_id);
		$settings->applyTo($obj_id);

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		return $this->showSettings();
	}

	protected function toChangeCourseData() {
		if($this->crs_ref_id !== null) {
			return $this->showSettings();
		}

		if($this->crs_request_id !== null) {
			return $this->showSettingsByRequestId();
		}
	}

	protected function getFormValuesByTemplateId($a_template_id, $trainer_ids) {
		$training_info = $this->dctl_utils->getTemplateInfoFor($this->current_user->getId(), $a_template_id);
		$uvg_org_units = gevUVGOrgUnits::getInstance();
		$crs_utils = gevCourseUtils::getInstance($a_template_id);

		$tmp = $crs_utils->getSchedule();
		$sched = explode("-",$tmp[0]);
		$training_info["start_datetime"] = new ilDateTime("1970-01-01 ".$sched[0].":00", IL_CAL_DATETIME);
		$training_info["end_datetime"] = new ilDateTime("1970-01-01 ".$sched[1].":00", IL_CAL_DATETIME);
		$training_info["invitation_preview"] = gevCourseUtils::getInstance($a_template_id)->getInvitationMailPreview();
		
		$training_info["credit_points"] = gevCourseUtils::getInstance($a_template_id)->getCreditPoints();
		$training_info["no_changes_allowed"] = false;
		$training_info["orgu_id"] = "";

		$trainer_orgus = array();
		foreach ($trainer_ids as $key => $trainer_id) {
			$usr_utils = gevUserUtils::getInstance($trainer_id);
			if($usr_utils->isUVGDBV()) {
				if($usr_utils->isSuperior()) {
					$sup_ids = array($usr_utils->getId());
				} else {
					$sup_ids = gevOrgUnitUtils::getSuperiorsOfUser($usr_utils->getId());
				}

				foreach ($sup_ids as $sup_id) {
					$pers_org_unit = $uvg_org_units->getOrgUnitIdOf($sup_id);
					if($pers_org_unit) {
						try{
							$above_ref_id = gevOrgUnitUtils::getBDOf(gevObjectUtils::getRefId($pers_org_unit));
							$trainer_orgus[] = $above_ref_id;
						} catch (Exception $e) {
							$this->log->write("no BD found for user: ".$usr_utils->getId());
						}
					}
				}
			} else {
				$trainer_orgus = null;
				break;
			}
		}
		
		if($trainer_orgus !== null) {
			$trainer_orgus = array_unique($trainer_orgus);
			if(count($trainer_orgus) == 1) {
				$training_info["orgu_id"] = $trainer_orgus[0];
			} else {
				$training_info["orgu_id"] = $uvg_org_units->getBaseRefId();
			}
		}
		return $training_info;
	}

	protected function getFormValuesByCrsObjId($obj_id) {
		$crs_utils = gevCourseUtils::getInstance($obj_id);
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
			, "webinar_link" => $crs_utils->getVirtualClassLink()
			, "webinar_password" => $crs_utils->getVirtualClassPassword()
			, "orgu_id" => $crs_utils->getTEPOrguId()
			, "invitation_preview" => $crs_utils->getInvitationMailPreview()
			, "orgaInfo" => $crs_utils->getOrgaInfo()
			, "credit_points" => $crs_utils->getCreditPoints()
			, "webinar_vc_type" => $crs_utils->getVirtualClassType()
			, "target_groups" => $crs_utils->getTargetGroup()
			, "trainer_ids" => $crs_utils->getTrainers()
			, "no_changes_allowed" => $crs_utils->isStarted()
			, "added_files" => $crs_utils->getCustomAttachments()
			);

		return $training_info;
	}

	protected function isCrsTemplateFlexible($obj_id) {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$crs_utils = gevCourseUtils::getInstance($obj_id);
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();
		$tmpl_id = gevObjectUtils::getObjId($crs_utils->getTemplateRefId());

		if($tmpl_id == $presence_flexible_tpl_id || $tmpl_id == $webinar_flexible_tpl_id) {
			return true;
		}

		return false;
	}

	protected function isTemplateFlexible($template_id) {
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();

		if($template_id == $presence_flexible_tpl_id || $template_id == $webinar_flexible_tpl_id) {
			return true;
		}

		return false;
	}

	protected function getFormValuesByRequestId($crs_request_id) {
		$request_db = new gevDecentralTrainingCreationRequestDB();
		$request = $request_db->request($crs_request_id);

		$training_info = array(
			  "title" => $request->settings()->title()
			, "description" => $request->settings()->description()
			, "date" => $request->settings()->start()
			, "start_datetime" => $request->settings()->start()
			, "end_datetime" => $request->settings()->end()
			, "time" => null
			, "venue" => $request->settings()->venueObjId()
			, "venue_free_text" => $request->settings()->venueText()
			, "webinar_link" => $request->settings()->webinarLink()
			, "webinar_password" => $request->settings()->webinarPassword()
			, "orgu_id" => $request->settings()->orguRefId()
			, "invitation_preview" => ""
			, "orgaInfo" => $request->settings()->orgaInfo()
			, "webinar_vc_type" => $request->settings()->vcType()
			, "target_groups" => $request->settings()->targetGroup()
			, "trainer_ids" => $request->trainerIds()
			, "no_changes_allowed" => false
			, "template_id" => $request->templateObjId()
			, "tmp_path_string" => $request->settings()->tmpPathString()
			, "added_files" => $request->settings()->addedFiles()
			);

		return $training_info;
	}

	protected function createMailPreview() {
		$tpl = new IlTemplate("tpl.dct_mail_preview.html",true,true,"Services/GEV/DecentralTrainings");
		require_once("Services/GEV/Utils/classes/class.gevMailUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings = gevSettings::getInstance();
		$mail_utils = gevMailUtils::getInstance();

		$mail_preview_json = $this->ctrl->getLinkTargetByClass("gevDecentralTrainingCreateMailPreviewDataGUI", 'createPreviewData', '', true);
		$tpl->setVariable("MAIL_PREVIEW_JSON", $mail_preview_json);

		//-2 = "keine E-Mail versenden"
		if($this->mail_tpl_id != -2) {
			//-1 = Nutze die Standardvorlage
			if($this->mail_tpl_id == -1) {
				require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
				$inv_mail_settings = new gevCrsInvitationMailSettings($this->template_id);
				$this->mail_tpl_id = $inv_mail_settings->getTemplateFor("standard");
			}

			//immer noch -1 = keine Email versenden an der Standardvorlage
			if($this->mail_tpl_id > 0) {
				$mail_tpl = $mail_utils->getMailTemplateByIdAndLanguage($this->mail_tpl_id,$this->lng->getLangKey());
				$tpl->setVariable("MAILTEMPLATE",nl2br($mail_tpl));
			} else {
				$tpl->setVariable("MAILTEMPLATE",$this->lng->txt("gev_dec_training_no_mail"));
			}
		} else {
			$tpl->setVariable("MAILTEMPLATE",$this->lng->txt("gev_dec_training_no_mail"));
		}

		if($this->template_id !== null){
			$tpl_ref = gevObjectUtils::getRefId($this->template_id);
			$tpl->setVariable("CRS_TPL","crs_template_id_".$tpl_ref);
		} else {
			if($this->crs_ref_id !== null) {
				$tpl->setVariable("CRS_REF","crs_ref_id_".$this->crs_ref_id);
			}

			if($this->crs_ref_id === null && $this->crs_request_id !== null) {
				$tpl->setVariable("CRS_REQUEST","crs_request_id_".$this->crs_request_id);
			}
		}
		
		return $tpl->get();
	}

	protected function forwardCrs() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
		$crs_utils = gevCourseUtils::getInstance($_POST["obj_id"]);
		$ref_id = gevObjectUtils::getRefId($_POST["obj_id"]);

		if($crs_utils->userHasPermissionTo($this->current_user->getId(),"read")){
			$this->ctrl->setParameterByClass("ilObjCourseGUI", "ref_id", $ref_id);
			$this->ctrl->redirectByClass(array("ilRepositoryGUI","ilObjCourseGUI"), "view");
		} else {
			$this->ctrl->setParameterByClass("ilInfoScreenGUI", "ref_id", $ref_id);
			$this->ctrl->redirectByClass(array("ilRepositoryGUI","ilObjCourseGUI","ilInfoScreenGUI"), "showSummary");
		}
	}

	protected function confirmTrainingCancellation() {
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt("gev_confirm_training_cancellation"));

		$conf->setConfirm($this->lng->txt("gev_cancel_training_action"), "cancelTraining");
		$conf->setCancel($this->lng->txt("cancel"), "showSettings");

		require_once("./Services/GEV/Utils/classes/class.gevCourseUtils.php");

		$crs_utils = gevCourseUtils::getInstance($_POST["obj_id"]);
		$conf->addItem("obj_id", $_POST["obj_id"]
					  , $crs_utils->getTitle()." (".$crs_utils->getFormattedAppointment().")"
					  );

		$this->tpl->setContent($conf->getHTML());	
	}

	protected function cancelTraining() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		gevCourseUtils::getInstance($_POST["obj_id"])->cancel();
		
		ilUtil::sendSuccess($this->lng->txt("gev_training_cancelled"), true);
		$this->ctrl->redirectByClass("ilTEPGUI");
	}

	/**
	 * Build the form used to upload an attachment and attach it to
	 * the toolbar. Will only do that once.
	 *
	 * @return ilFileInputGUI The upload form.
	 */
	protected function createAttachmentUploadForm($no_changes_allowed) {
		require_once("Services/CaTUIComponents/classes/class.catFileInputGUI.php");

		$file_upload_form = new catFileInputGUI();
		$file_upload_form->setPostVar("attachment_upload");
		$file_upload_form->setMulti(true);
		$file_upload_form->setDisabled($no_changes_allowed);
		
		return $file_upload_form;
	}

	protected function uploadFiles($files, $folder_string = null) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingFileStorage.php");
		if($folder_string === null) {
			$folder_string = $this->randomstring();
		}
		
		$file_storage = new gevDecentralTrainingFileStorage($folder_string);
		
		foreach ($files as $key => $value) {
			$file_storage->addFile($value["tmp_name"],$value["name"]);
		}
		
		return $folder_string;
	}

	protected function randomstring($length = 6) {
		// $chars - String aller erlaubten Zahlen
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
		
		srand((double)microtime()*1000000);
		$i = 0;
		while ($i < $length) {
			$num = rand() % strlen($chars);
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}

		return $pass;
	}

	protected function switchUploadFileArray(array $files) {
		$amount_of_files = count($files["name"]);
		$ret = array();
		for($i = 0; $i < $amount_of_files; $i++) {
			$ret[$i] = array();
			foreach ($files as $key => $value) {
				$ret[$i][$key] = $value[$i];
			}
			if($ret[$i]["error"] == self::UPLOAD_ERROR_VALUE) {
				unset($ret[$i]);
			}
		}
		return $ret;
	}

	protected function splitNewFiles(array $files) {
		$ret = array();

		foreach ($files as $key => $value) {
			$ret[$value["name"]] = $value["name"];
		}

		return $ret;
	}

	protected function deleteRemovedFiles($folder_string, array $files = null) {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingFileStorage.php");
		$file_storage = new gevDecentralTrainingFileStorage($folder_string);
		if($files === null) {
			$file_storage->deleteDirectory();
		}

		$current_files = $file_storage->getAllFiles();
		if($current_files) {
			foreach ($current_files as $key => $value) {
				if($value == "." || $value == "..") {
					continue;
				}
				if(!in_array($value,$files)) {
					$file_storage->deleteFile($value);
				}
			}
		}
	}

	protected function attachmentHandling() {
		$old_files = array();
		$new_files = array();
		$files_new_for_form = array();
		if($this->added_files) {
			foreach ($this->added_files as $key => $value) {
				$old_files[$value] = $value;
			}
		}

		$this->deleteRemovedFiles($this->tmp_path_string, $this->added_files);

		if(isset($_FILES["attachment_upload"])){
			$new_files = $this->switchUploadFileArray($_FILES["attachment_upload"]);
			
			if(count($new_files)>0) {
				$this->tmp_path_string = $this->uploadFiles($new_files, $this->tmp_path_string);
				$files_new_for_form = $this->splitNewFiles($new_files);
			}
			$_POST["tmp_path_string"] = $this->tmp_path_string;
		}

		$this->added_files = array_merge($old_files, $files_new_for_form);
		return $this->added_files;
	}

	protected function addUploadedFileGUI($key, $value, $no_changes_allowed) {
		$file = new catUploadedFilesGUI("", "added_files[]", false);
		$file->setValue($value);
		$file->setBtnValue($key);
		$file->setBtnDescription($this->lng->txt("gev_dec_training_attachment_delete"));
		$file->showBtn(true);
		$file->setDisabled($no_changes_allowed);

		return $file;
	}

	protected function handleCustomAttachments() {
		$old_files = array();
		if($this->added_files) {
			foreach ($this->added_files as $key => $value) {
				$old_files[$value] = $value;
			}
		}

		$this->deleteRemovedCustomAttachments();
		$files_new_for_form = $this->addCustomAttachments();


		$this->added_files = array_merge($old_files, $files_new_for_form);
		
		return $this->added_files;
	}

	protected function deleteRemovedCustomAttachments() {
		$obj_id = gevObjectUtils::getObjId($this->crs_ref_id);
		$crs_utils = gevCourseUtils::getInstance($obj_id);

		$old_files = $crs_utils->getCustomAttachments();

		if($this->added_files === null) {
			 $removed_files = $old_files;
		} else {
			$removed_files = array_diff($old_files,$this->added_files);
		}
		
		if(count($removed_files) > 0) {
			//Reihenfolge wichtig!!!!
			//1. Die Auswahl in den Einladungsmails entfernen
			//2. Mailanhang löschen
			//3. Anhang aus Custom Tabelle löschen
			$crs_utils->removePreselectedAttachments(array(gevCourseUtils::RECIPIENT_MEMBER,gevCourseUtils::RECIPIENT_STANDARD), $removed_files);
			$crs_utils->removeAttachmentsFromMail($removed_files);
			$crs_utils->deleteCustomAttachment($removed_files);
		}
	}

	protected function addCustomAttachments() {
		$obj_id = gevObjectUtils::getObjId($this->crs_ref_id);
		$crs_utils = gevCourseUtils::getInstance($obj_id);

		$files_new_for_form = array();

		if(isset($_FILES["attachment_upload"])){
			$new_files = $this->switchUploadFileArray($_FILES["attachment_upload"]);
			
			if(count($new_files)>0){
				$this->tmp_path_string = $crs_utils->addAttachmentsToMailSeperateFolder($new_files);
				$files_new_for_form = $this->splitNewFiles($new_files);
				$crs_utils->addPreselectedAttachments(array(gevCourseUtils::RECIPIENT_MEMBER,gevCourseUtils::RECIPIENT_STANDARD), $files_new_for_form);
				$crs_utils->saveCustomAttachments($files_new_for_form);
			}
		}

		return $files_new_for_form;
	}

	protected function deliverAttachment() {

		if(isset($_GET["crs_id"])) {
			$crs_utils = gevCourseUtils::getInstance($_GET["crs_id"]);
			$crs_utils->deliverAttachment($_GET["filename"]);
		}
		
		if(isset($_GET["request_id"])) {
			require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingUtils.php");
			$dct_utils = gevDecentralTrainingUtils::getInstance();
			$dct_utils->deliverAttachment($_GET["filename"], $_GET["request_id"]);
		}
	}
}
