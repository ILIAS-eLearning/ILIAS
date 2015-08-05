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
	const MAX_COURSE_DURATION = 720;
	
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
		$this->tpl_date_auto_change = null;

		$this->dctl_utils = gevDecentralTrainingUtils::getInstance();

		$this->tpl->getStandardTemplate();
		$this->tpl->addJavaScript('./Services/GEV/DecentralTrainings/js/dct_date_duration_update.js');
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
			case "addBuildingBlock":
			case "updateBuildingBlock":
			case "showBuildingBlock":
			case "updateCourseData":
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
		

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();

		if($template_id == $presence_flexible_tpl_id || $template_id == $webinar_flexible_tpl_id) {
			$form->addCommandButton("addBuildingBlock", $this->lng->txt("gev_dec_training_add_buildingblocks"));
		} else {
			$form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		}
		
		$form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$form->setFormAction($this->ctrl->getFormAction($this));


		
		$ret = $title->render()
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

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();

		$form_tpl_id = $a_form->getInput("template_id");

		if($form_tpl_id == $presence_flexible_tpl_id || $form_tpl_id == $webinar_flexible_tpl_id) {
			$a_form->addCommandButton("addBuildingBlock", $this->lng->txt("gev_dec_training_add_buildingblocks"));
		} else {
			$a_form->addCommandButton("finalizeTrainingCreation", $this->lng->txt("gev_dec_training_creation"));
		}

		$a_form->addCommandButton("cancel", $this->lng->txt("cancel"));
		$a_form->setFormAction($this->ctrl->getFormAction($this));

		if($this->tpl_date_auto_change !== null) {
			$js = $this->tpl_date_auto_change->get();
		}
		else {
			$js = "";
		}

		return   $title->render()
				.$a_form->getHTML()
				.$js;
	}
	
	protected function finalizeTrainingCreation() {
		if (!$this->dctl_utils->userCanOpenNewCreationRequest()) {
			return $this->ctrl->redirect($this, "showOpenRequests");
		}
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequest.php");

		$form_prev = $this->buildTrainingOptionsForm(false,null,$_POST["trainer_ids"],$_POST["date"],$_POST["template_id"]);
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		if (!$form_prev->checkInput()) {
			return $this->failCreateTraining($form_prev);
		}
		
		$template_id = intval($form_prev->getInput("template_id"));
		if(!$this->checkDecentralTrainingConstraints($form_prev, $template_id)) {
			return $this->failCreateTraining($form_prev);
		}
		
		$trainer_ids = array_map(function($inp) {return (int)$inp; }
								, unserialize(base64_decode($form_prev->getInput("trainer_ids")))
								);
		
		$crs_utils = gevCourseUtils::getInstance($template_id);
		$settings = $this->getSettingsFromForm($crs_utils, $form_prev, $template_id);
		$creation_request = new gevDecentralTrainingCreationRequest
									( $dec_utils->getCreationRequestDB()
									, (int)$this->current_user->getId()
									, (int)$template_id
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
		$crs_utils = gevCourseUtils::getInstance($obj_id);
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();
		$tmpl_id = gevObjectUtils::getObjId($crs_utils->getTemplateRefId());

		$start_date = $crs_utils->getStartDate()->get(IL_CAL_DATE);
		$now = date("Y-m-d");
		$should_save = $start_date > $now;
		
		if($tmpl_id == $presence_flexible_tpl_id || $tmpl_id == $webinar_flexible_tpl_id) {
			if($should_save) {
				$this->ctrl->setParameter($this,"ref_id", $_GET["ref_id"]);
				$form->addCommandButton("updateCourseData", $this->lng->txt("save"));
				$form->addCommandButton("updateBuildingBlock", $this->lng->txt("gev_dec_training_update_buildingblocks"));
			} else {
				$form->addCommandButton("showBuildingBlock", $this->lng->txt("gev_dec_training_show_buildingblocks"));
			}
		} else {
			if($should_save) {
				$form->addCommandButton("updateSettings", $this->lng->txt("save"));
			}
		}
		

		$form->addCommandButton("cancel", $this->lng->txt("back"));
		$form->setFormAction($this->ctrl->getFormAction($this));
				
		$title = new catTitleGUI("gev_dec_training_settings_header"
								, "gev_dec_training_settings_header_note"
								, "GEV_img/ico-head-create-decentral-training.png"
								);
	
	
		$ret = $title->render()
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
		
		$form = $this->buildTrainingOptionsForm(false, $_POST["obj_id"]);

		//gev patch start
		$form->setValuesByPost();

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

		if($is_flexible) {
			$title = $a_form->getInput("title");
			$tc = $a_form->getInput("training_category");
			$training_category = $tc ? $tc : array();
			$tg = $a_form->getInput("target_groups");
			$target_group = $tg ? $tg : array();
			$gdv_topic_temp = $a_form->getInput("gdv_topic");
			$gdv_topic = ($gdv_topic_temp != "0") ? $gdv_topic_temp : null;
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
			$templates[$info["ltype"]][$info["obj_id"]] = $info["title"];
		}
		
		//load templates id's for flexible trainings
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();
		$should_see_presence_flexible = false;
		$should_see_webinar_flexible = false;

		$ltype_choice = new ilRadioGroupInputGUI($this->lng->txt("gev_course_type"), "ltype");
		$form->addItem($ltype_choice);
		$selected = "presence_flexible";

		foreach ($templates as $ltype => $tmplts) {
			if(array_key_exists($presence_flexible_tpl_id,$tmplts)) {
				$should_see_presence_flexible = true;
				unset($tmplts[$presence_flexible_tpl_id]);
			}

			if(array_key_exists($webinar_flexible_tpl_id,$tmplts)) {
				$should_see_webinar_flexible = true;
				unset($tmplts[$webinar_flexible_tpl_id]);
			}

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

			if(empty($tmplts)) {
				if($selected != "präsenztraining") {
					$selected = "presence_flexible";
				}
				continue;
			}

			$key = strtolower(str_replace(" ", "_", $ltype));
			$ltype_opt = new ilRadioOption($desc, $key);
			$ltype_choice->addOption($ltype_opt);
			
			$training_select = new ilSelectInputGUI($this->lng->txt("gev_dec_training_template"), $key."_template");
			$training_select->setOptions($tmplts);

			$ltype_opt->addSubItem($training_select);
		}
		
	
		//Präsenztraining Flexsibel
		if($should_see_presence_flexible) {
			$presence_flexible = new ilRadioOption($this->lng->txt("gev_dec_training_presence_flex"), "presence_flexible");
			$ltype_choice->addOption($presence_flexible);
		
			$presence_flexible_tpl_id_hidden = new ilHiddenInputGUI("presence_flexible_template");
			$presence_flexible_tpl_id_hidden->setValue($presence_flexible_tpl_id);
			$form->addItem($presence_flexible_tpl_id_hidden);
		}
		
		
		//Webinar Flexsibel
		if($should_see_webinar_flexible) {
			$webinar_flexible = new ilRadioOption($this->lng->txt("gev_dec_training_webinar_flex"), "webinar_flexible");
			$ltype_choice->addOption($webinar_flexible);
		
			$webinar_flexible_tpl_id_hidden = new ilHiddenInputGUI("webinar_flexible_template");
			$webinar_flexible_tpl_id_hidden->setValue($webinar_flexible_tpl_id);
			$form->addItem($webinar_flexible_tpl_id_hidden);
		}

		$ltype_choice->setValue($selected);
		
		
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
		if ($a_training_id === null && $a_template_id === null && $a_fill) {
			throw new Exception("gevDecentralTrainingGUI::buildTrainingOptionsForm: Either set training_id or template_id.");
		}
		
		if ($a_template_id !== null && $a_trainer_ids === null && $a_fill) {
			throw new Exception("gevDecentralTrainingGUI::buildTrainingOptionsForm: You need to set trainer_ids if you set a template_id.");
		}
		
		$a_template_obj_id = $a_template_id;
		
		$crs_utils = null;
		
		if($a_template_id !== null) {
			$crs_utils = gevCourseUtils::getInstance((int)$a_template_id);
		}

		if($a_training_id !== NULL && $a_template_id === null) {
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");

			$crs_utils = gevCourseUtils::getInstance($a_training_id);
			$a_template_ref_id = $crs_utils->getTemplateRefId();
			$a_template_obj_id = gevObjectUtils::getObjId($a_template_ref_id);
		}

		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		$settings_utils = gevSettings::getInstance();
		$presence_flexible_tpl_id = $settings_utils->getDctTplFlexPresenceObjId();
		$webinar_flexible_tpl_id = $settings_utils->getDctTplFlexWebinarObjId();

		if($a_template_obj_id == $presence_flexible_tpl_id || $a_template_obj_id == $webinar_flexible_tpl_id) {
			return $this->buildTrainingOptionsFormFlexible($a_fill, $a_training_id, $a_trainer_ids, $a_date, $a_template_id);
		}
		
		if($crs_utils !== null) {
			$credit_points = $crs_utils->getCreditPoints();
			if($credit_points !== null && $credit_points > 0) {
				$this->tpl_date_auto_change = new ilTemplate("tpl.gev_dct_duration_update_js.html", false, false, "Services/GEV/DecentralTrainings");
			}
		}
		
		return $this->buildTrainingOptionsFormStable($a_fill, $a_training_id, $a_trainer_ids, $a_date, $a_template_id);
	}

	protected function buildTrainingOptionsFormStable($a_fill = false, $a_training_id = null, $a_trainer_ids = null, $a_date = null, $a_template_id = null) {
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
		
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_settings"));

		if ($a_fill) {
			if ($a_template_id !== null) {
				$training_info = $dec_utils->getTemplateInfoFor($this->current_user->getId(), $a_template_id);
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

		/*************************
		* TITEL
		*************************/
		$title_section = new ilFormSectionHeaderGUI();
		$title_section->setTitle($this->lng->txt("gev_dec_training_title"));
		$form->addItem($title_section);

		$title = new ilNonEditableValueGUI($this->lng->txt("title"), "title", false);
		$title->setValue($training_info["title"]);
		$form->addItem($title);
		
		$description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		if ($a_fill) {
			$description->setValue($training_info["description"]);
		}
		$description->setDisabled($no_changes_allowed);
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
		require_once("Services/TEP/classes/class.ilTEP.php");
		$org_info = ilTEP::getPossibleOrgUnitsForTEPEntriesSeparated();
		require_once "Services/TEP/classes/class.ilTEPOrgUnitSelectionInputGUI.php";
		$orgu_selection = new ilTEPOrgUnitSelectionInputGUI($org_info, "orgu_id", false, false);
		if ($a_fill) {
			$orgu_selection->setValue($training_info["orgu_id"]);
		}
		$orgu_selection->setRecursive(false);
		$orgu_selection->setRequired(true);
		$form->addItem($orgu_selection);

		/*************************
		* ORGANISTION
		*************************/
		$orga_section = new ilFormSectionHeaderGUI();
		$orga_section->setTitle($this->lng->txt("gev_dec_training_orga"));
		$form->addItem($orga_section);

		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_course_type"), "ltype", false);
		$ltype->setValue($training_info["ltype"]);
		$form->addItem($ltype);

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

	protected function buildTrainingOptionsFormFlexible($a_fill = false, $a_training_id = null, $a_trainer_ids = null, $a_date = null, $a_template_id = null) {
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
		require_once("Services/GEV/Utils/classes/class.gevSettings.php");
		require_once("Services/GEV/Utils/classes/class.gevAMDUtils.php");
		
		$amd_utils = gevAMDUtils::getInstance();
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		$form = new catPropertyFormGUI();
		$form->setTemplate("tpl.gev_dec_training_choose_template_form.html", "Services/GEV/Desktop");
		$form->setTitle($this->lng->txt("gev_dec_training_settings"));
		
		if ($a_fill) {
			if ($a_template_id !== null) {
				$training_info = $dec_utils->getTemplateInfoFor($this->current_user->getId(), $a_template_id);
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
					, "webinar_vc_type" => $crs_utils->getWebExVirtualClassType()
					, "training_category" => $crs_utils->getTopics()
					, "target_groups" => $crs_utils->getTargetGroup()
					, "gdv_topic" => $crs_utils->getWBDTopic()
					);
				$trainer_ids = $crs_utils->getTrainers();
				$no_changes_allowed = $crs_utils->isFinalized();
			}
		}
		else {
			if ($a_template_id !== null) {
				$crs_utils = gevCourseUtils::getInstance($a_template_id);
				$no_changes_allowed = false;
			} else {
				$crs_utils = gevCourseUtils::getInstance($a_training_id);
				$no_changes_allowed = false;
			}
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
		
		/*************************
		* TITEL
		*************************/
		$title_section = new ilFormSectionHeaderGUI();
		$title_section->setTitle($this->lng->txt("gev_dec_training_title"));
		$form->addItem($title_section);

		$title = new ilTextInputGUI($this->lng->txt("title"), "title");
		if($training_info["title"] && $a_fill) {
			$title->setValue($training_info["title"]);
		}
			
		$title->setRequired(true);
		$form->addItem($title);
		
		$description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		if ($a_fill) {
			$description->setValue($training_info["description"]);
		}
		$description->setDisabled($no_changes_allowed);
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
			$date->setDate($training_info["date"]);
		}
		$date->setRequired(true);
		$date->setDisabled($no_changes_allowed);
		$form->addItem($date);

		$time = new ilDateDurationInputGUI($this->lng->txt("gev_duration"), "time");
		$time->setShowDate(false);
		$time->setShowTime(true);
		if ($a_fill) {
			$time->setStart($training_info["start_datetime"]);
			$time->setEnd($training_info["end_datetime"]);
		}
		$time->setRequired(true);
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
		require_once("Services/TEP/classes/class.ilTEP.php");
		$org_info = ilTEP::getPossibleOrgUnitsForTEPEntriesSeparated();
		require_once "Services/TEP/classes/class.ilTEPOrgUnitSelectionInputGUI.php";
		$orgu_selection = new ilTEPOrgUnitSelectionInputGUI($org_info, "orgu_id", false, false);
		if ($a_fill) {
			$orgu_selection->setValue($training_info["orgu_id"]);
		}
		$orgu_selection->setRecursive(false);
		$orgu_selection->setRequired(true);
		$form->addItem($orgu_selection);
		
		
		/*************************
		* ORGANISTION
		*************************/
		$orga_section = new ilFormSectionHeaderGUI();
		$orga_section->setTitle($this->lng->txt("gev_dec_training_orga"));
		$form->addItem($orga_section);

		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_course_type"), "ltype", false);
		$ltype->setValue($training_info["ltype"]);
		$form->addItem($ltype);

		if ($crs_utils->isWebinar()) {
			//$vc_type_options = $amd_utils->getOptions(gevSettings::CRS_AMD_WEBEX_VC_CLASS_TYPE);
			// For now we'll only allow csn and webex
			$vc_type_options = array("CSN"=>"CSN","Webex"=>"Webex");
			$webinar_vc_type = new ilSelectInputGUI($this->lng->txt("gev_dec_training_vc_type"),"webinar_vc_type");
			$options = array(0 => "-") + $vc_type_options;
			$webinar_vc_type->setOptions($options);
			if($training_info["webinar_vc_type"] && $a_fill){
				$webinar_vc_type->setValue($training_info["webinar_vc_type"]);
			}
			$form->addItem($webinar_vc_type);

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

		//organisatorisches
		$orgaInfo = new ilTextAreaInputGUI($this->lng->txt("gev_orga_info"),"orgaInfo");
		if ($training_info["orgaInfo"] && $a_fill) {
				$orgaInfo->setValue($training_info["orgaInfo"]);
			}
		$orgaInfo->setUseRte(true);
		$form->addItem($orgaInfo);
		
		/*************************
		* INHALT
		*************************/
		$content_section = new ilFormSectionHeaderGUI();
		$content_section->setTitle($this->lng->txt("gev_dec_training_content"));
		$form->addItem($content_section);

		//trainingskategorie
		$training_cat = $amd_utils->getOptions(gevSettings::CRS_AMD_TOPIC);
		$cbx_group_training_cat = new ilCheckBoxGroupInputGUI($this->lng->txt("gev_dec_training_training_category"),"training_category");
		$cbx_group_training_cat->setRequired(true);

		foreach($training_cat as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			$cbx_group_training_cat->addOption($option);
		}

		if($training_info["training_category"] && $a_fill) {
			$cbx_group_training_cat->setValue($training_info["training_category"]);
		}
		$form->addItem($cbx_group_training_cat);

		//zielgruppe
		$target_groups = $amd_utils->getOptions(gevSettings::CRS_AMD_TARGET_GROUP);
		$cbx_group_target_groups = new ilCheckBoxGroupInputGUI($this->lng->txt("gev_dec_training_target_groups"),"target_groups");

		foreach($target_groups as $value => $caption)
		{
			$option = new ilCheckboxOption($caption, $value);
			$cbx_group_target_groups->addOption($option);
		}

		if($training_info["target_groups"] && $a_fill){
			$cbx_group_target_groups->setValue($training_info["target_groups"]);
		}
		$form->addItem($cbx_group_target_groups);

		/*************************
		* BEWERTUNG
		*************************/
		$rating_section = new ilFormSectionHeaderGUI();
		$rating_section->setTitle($this->lng->txt("gev_dec_training_rating"));
		$form->addItem($rating_section);

		//GDV Lerninhalt
		$gdv_topic_options = $amd_utils->getOptions(gevSettings::CRS_AMD_GDV_TOPIC);
		$gdv_topic = new ilSelectInputGUI($this->lng->txt("gev_dec_training_gdv_topic"),"gdv_topic");
		$options = array("" => "-") + $gdv_topic_options;
		$gdv_topic->setOptions($options);
		$gdv_topic->setRequired(true);
		if($training_info["gdv_topic"] && $a_fill){
			$gdv_topic->setValue($training_info["gdv_topic"]);
		}
		$form->addItem($gdv_topic);
		
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

	protected function addBuildingBlock() {
		$form = $this->buildTrainingOptionsForm(false,null,$_POST["trainer_ids"],$_POST["date"],$_POST["template_id"]);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->failCreateTraining($form);
		}
		
		$template_id = intval($form->getInput("template_id"));
		if(!$this->checkDecentralTrainingConstraints($form, $template_id)) {
			return $this->failCreateTraining($form);
		}

		$trainer_ids = array_map(function($inp) {return (int)$inp; }
								, unserialize(base64_decode($form->getInput("trainer_ids")))
								);

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCreationRequest.php");
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		$crs_utils = gevCourseUtils::getInstance($_POST["template_id"]);
		$settings = $this->getSettingsFromForm($crs_utils, $form, $_POST["template_id"]);
		$creation_request = new gevDecentralTrainingCreationRequest
									( $dec_utils->getCreationRequestDB()
									, (int)$this->current_user->getId()
									, (int)$_POST["template_id"]
									, $trainer_ids
									, $settings
									);
		$creation_request->save();

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlockGUI.php");
		$bb_gui = new gevDecentralTrainingCourseCreatingBuildingBlockGUI(null, $creation_request->requestId());
		$this->ctrl->forwardCommand($bb_gui);
	}

	protected function updateBuildingBlock() {
		$obj_id = (int)$_POST["obj_id"];
		
		$form = $this->buildTrainingOptionsForm(false, $obj_id);
		$form->setValuesByPost();

		if (!$form->checkInput()) {
			return $this->showSettings($form);
		}
		
		if(!$this->checkDecentralTrainingConstraints($form, $obj_id)) {
			return $this->showSettings($form);
		}

		//HIER IRGENDWAS WEGEN MAILS
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

		$settings = $this->getSettingsFromForm($crs_utils, $form, $tmpl_id);
		$settings->applyTo((int)$_POST["obj_id"]);

		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlockGUI.php");
		$bb_gui = new gevDecentralTrainingCourseCreatingBuildingBlockGUI($_POST["obj_id"]);
		$this->ctrl->forwardCommand($bb_gui);
	}

	protected function showBuildingBlock() {
		require_once("Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingCourseCreatingBuildingBlockGUI.php");
		$bb_gui = new gevDecentralTrainingCourseCreatingBuildingBlockGUI($_POST["obj_id"]);
		$this->ctrl->forwardCommand($bb_gui);
	}

	function updateCourseData() {
		$obj_id = (int)$_POST["obj_id"];
		
		$form = $this->buildTrainingOptionsForm(false, $obj_id);
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

		$settings = $this->getSettingsFromForm($crs_utils, $form, $tmpl_id);
		$settings->applyTo($obj_id);

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		return $this->showSettings();
	}
}

?>