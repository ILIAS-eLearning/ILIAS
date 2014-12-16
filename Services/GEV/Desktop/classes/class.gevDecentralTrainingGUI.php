<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Forms for decentral trainings.
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
require_once("Services/GEV/Utils/classes/class.gevDecentralTrainingUtils.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/CaTUIComponents/classes/class.catPropertyFormGUI.php");

class gevDecentralTrainingGUI {
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

		$this->tpl->getStandardTemplate();
	}

	public function executeCommand() {
		$this->loadUserId();
		$this->loadDate();
		
		$this->checkCanCreateDecentralTraining();
		
		$cmd = $this->ctrl->getCmd();
		
		switch($cmd) {
			case "chooseTemplateAndTrainers":
			case "createTraining":
			case "finalizeTrainingCreation":
			case "cancel":
			case "backFromBooking":
			case "showSettings":
			case "updateSettings":
				$cont = $this->$cmd();
			default:
				$this->log->write("gevDecentralTrainingGUI: Unknown command '".$this->cmd."'");
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
	
	protected function checkCanCreateDecentralTraining() {
		// TODO
	}
	
	protected function cancel() {
		$this->ctrl->redirectByClass("ilTEPGUI");
	}
	
	protected function chooseTemplateAndTrainers($a_form = null) {
		$title = new catTitleGUI("gev_dec_training_creation", "gev_dec_training_creation_header_note", "GEV_img/ico-head-create-decentral-training.png");
		
		$form = ($a_form === null) ? $this->buildChooseTemplateAndTrainersForm($this->user_id, $this->date)
								   : $a_form;
		
		return   $title->render()
				.$form->getHTML();
	}
	
	protected function createTraining($a_form = null) {
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
	
	protected function finalizeTrainingCreation() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		$form_prev = $this->buildTrainingOptionsForm(false);
		$dec_utils = gevDecentralTrainingUtils::getInstance();
		
		if (!$form_prev->checkInput()) {
			return $this->createTraining($form_prev);
		}
		
		$template_id = intval($form_prev->getInput("template_id"));
		$trainer_ids = unserialize(base64_decode($form_prev->getInput("trainer_ids")));
		
		$res = $dec_utils->create($this->current_user->getId(), $template_id, $trainer_ids);
		
		$this->updateSettingsFromForm($res["obj_id"], $form_prev);
		
		ilUtil::sendSuccess($this->lng->txt("gev_dec_training_creation_successfull"), true);
		
		require_once("Services/CourseBooking/classes/class.ilCourseBookingAdminGUI.php");
		require_once("Services/CourseBooking/classes/class.ilCourseBookingPermissions.php");
		
		if (ilCourseBookingPermissions::getInstanceByRefId($res["ref_id"], $this->current_user->getId())->bookCourseForOthers()) {
			ilUtil::sendInfo($this->lng->txt("gev_dev_training_book_users"), true);
			$this->ctrl->setParameter($this, "obj_id", $res["obj_id"]);
			ilCourseBookingAdminGUI::setBackTarget(
				$this->ctrl->getLinkTarget($this, "backFromBooking")
				);
			$this->ctrl->setParameter($this, "obj_id", null);
			
			$this->ctrl->setParameterByClass("ilCourseBookingGUI", "ref_id", $res["ref_id"]);
			$this->ctrl->redirectByClass(array("ilCourseBookingGUI", "ilCourseBookingAdminGUI"));
		}
		else {
			return $this->backFromBooking();
		}
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
		if (!gevCourseUtils::getInstance($obj_id)->mailCronJobDidRun()) {
			$form->addCommandButton("updateSettings", $this->lng->txt("save"));
		}
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
		require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
		$mail_settings = new gevCrsAdditionalMailSettings($_POST["obj_id"]);
		
		$form = $this->buildTrainingOptionsForm(false, $_POST["obj_id"]);
		
		$form->setValuesByPost();
		$suppress_mail = $form->getItemByPostVar("suppress_mails");
		$suppress_mail->setChecked($mail_settings->getSuppressMails());
		if (!$form->checkInput()) {
			return $this->showSettings($form);
		}
		
		if (!$this->access->checkAccess("write_reduced_settings", "", gevObjectUtils::getRefId($_POST["obj_id"]))) {
			$this->log->write("gevDecentralTrainingGUI::updateSettings: User ".$this->current_user->getId()
							 ." tried to update Settings but has no permission.");
			throw new Exception("gevDecentralTrainingGUI::updateSettings: no permission");
		}
		$this->updateSettingsFromForm(intval($_POST["obj_id"]), $form);
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
		return $this->showSettings($form);
	}
	
	protected function updateSettingsFromForm($a_obj_id, $a_form) {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		$crs_utils = gevCourseUtils::getInstance($a_obj_id);
		$crs = $crs_utils->getCourse();
		$mail_settings = new gevCrsAdditionalMailSettings($a_obj_id);
		
		if ($crs_utils->mailCronJobDidRun()) {
			throw new Exception("gevDecentralTrainingGUI::updateSettingsFromForm: not update allowed, mail cron job already ran.");
		}
		
		$crs->setDescription($a_form->getInput("description"));
		
		$tmp = $a_form->getInput("date");
		$date = new ilDate($tmp["date"], IL_CAL_DATE);
		$crs_utils->setStartDate($date);
		$crs_utils->setEndDate($date);
		
		$tmp = $a_form->getInput("time");
		$start = split(":", $tmp["start"]["time"]);
		$end = split(":", $tmp["end"]["time"]);
		$time = array($start[0].":".$start[1]."-".$end[0].":".$end[1]);
		$crs_utils->setSchedule($time);
		
		if ($crs_utils->isPraesenztraining()) {
			$crs_utils->setVenueId($a_form->getInput("venue"));
		}
		
		if ($crs_utils->isWebinar()) {
			$link = $a_form->getInput("webinar_link");
			$crs_utils->setWebExLink($link ? $link : " ");
			$password = $a_form->getInput("webinar_password");
			$crs_utils->setWebExPassword($password ? $password : " ");
		}
		
		if (!$mail_settings->getSuppressMails()) {
			$this->mail_settings->setSuppressMails($a_form->getInput("suppress_mails"));
		} 
		
		$crs->update();
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
				$trainer_ids = $a_trainer_ids;
				$training_info["date"] = ($a_date !== null) ? new ilDate($a_date, IL_CAL_DATE)
															: new ilDate(date("Y-m-d"), IL_CAL_DATE);
				$training_info["start_datetime"] = new ilDateTime("1970-01-01 09:00:00", IL_CAL_DATETIME);
				$training_info["end_datetime"] = new ilDateTime("1970-01-01 18:00:00", IL_CAL_DATETIME);
				$training_info["invitation_preview"] = gevCourseUtils::getInstance($a_template_id)->getInvitationMailPreview();
				$no_changes_allowed = false;
				
				$tmplt_id = new ilHiddenInputGUI("template_id");
				$tmplt_id->setValue($a_template_id);
				$form->addItem($tmplt_id);
				
				$trnrs = new ilHiddenInputGUI("trainer_ids");
				$trnrs->setValue(base64_encode(serialize($a_trainer_ids)));
				$form->addItem($trnrs);
				
				$crs_utils = gevCourseUtils::getInstance($a_template_id);
			}
			else {
				require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
				$crs_utils = gevCourseUtils::getInstance($a_training_id);
				$mail_settings = new gevCrsAdditionalMailSettings($a_training_id);
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
					, "webinar_link" => $crs_utils->getWebExLink()
					, "webinar_password" => $crs_utils->getWebExPassword()
					, "invitation_preview" => $crs_utils->getInvitationMailPreview()
					, "suppress_mails" => $mail_settings->getSuppressMails()
					);
				$trainer_ids = $crs_utils->getTrainers();
				// TODO: this needs to be true when first mail cronjob was run.
				$no_changes_allowed = $crs_utils->mailCronJobDidRun();
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
				require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
				$mail_settings = new gevCrsAdditionalMailSettings($a_training_id);
				$training_info = array(
					  "ltype" => $crs_utils->getType()
					, "invitation_preview" => $crs_utils->getInvitationMailPreview()
					, "suppress_mails" => $mail_settings->getSuppressMails()
					);
				$no_changes_allowed = $crs_utils->mailCronJobDidRun();
			}
		}
		
		$title = new ilNonEditableValueGUI($this->lng->txt("title"), "", false);
		if ($a_fill) {
			$title->setValue($training_info["title"]);
		}
		$title->setDisabled($no_changes_allowed);
		$form->addItem($title);
		
		$description = new ilTextAreaInputGUI($this->lng->txt("description"), "description");
		if ($a_fill) {
			$description->setValue($training_info["description"]);
		}
		$description->setDisabled($no_changes_allowed);
		$form->addItem($description);
		
		$ltype = new ilNonEditableValueGUI($this->lng->txt("gev_course_type"), "", false);
		if ($a_fill) {
			$ltype->setValue($training_info["ltype"]);
		}
		$ltype->setDisabled($no_changes_allowed);
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
		
		$trainers = new ilNonEditableValueGUI($this->lng->txt("tutor"), "", true);
		if ($a_fill) {
			$trainers->setValue(implode("<br />", gevUserUtils::getFullNames($trainer_ids)));
		}
		$trainers->setDisabled($no_changes_allowed);
		$form->addItem($trainers);
		
		if ($crs_utils->isPraesenztraining()) {
			$venue = new ilSelectInputGUI($this->lng->txt("gev_venue"), "venue");
			$venue->setOptions(gevOrgUnitUtils::getVenueNames());
			if ($training_info["venue"] && $a_fill) {
				$venue->setValue($training_info["venue"]);
			}
			$venue->setDisabled($no_changes_allowed);
			$form->addItem($venue);
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
		
		$mail_section = new ilFormSectionHeaderGUI();
		$mail_section->setTitle($this->lng->txt("gev_mail_mgmt"));
		$form->addItem($mail_section);
		
		if ($training_info["invitation_preview"]) {
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
		
		$suppress_mails = new ilCheckboxInputGUI($this->lng->txt("gev_suppress_mails"), "suppress_mails");
		$suppress_mails->setOptionTitle($this->lng->txt("gev_suppress_mails_info"));
		$suppress_mails->setChecked($training_info["suppress_mails"]);
		$suppress_mails->setDisabled($training_info["suppress_mails"] || $no_changes_allowed);
		$form->addItem($suppress_mails);
		
		return $form;
	}
}

?>