<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("Services/Mailing/classes/class.ilMailingGUI.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

/**
* Class gevCrsMailingGUI
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

class gevCrsMailingGUI extends ilMailingGUI {
	protected function attachmentsSubtabVisible() {
		return true;
	}

	protected function autoMailsSubtabVisible() {
		return true;
	}

	protected function mailToMembersSubtabVisible() {
		return true;
	}

	protected function mailLogSubtabVisible() {
		return true;
	}

	protected function invitationMailTabVisible() {
		return true;
	}
	
	protected function additionalSettingsTabVisible() {
		return true;
	}

	protected function getMemberUserIds() {
		return $this->getCourse()->getMembersObject()->getParticipants();
	}

	protected function initMailAttachments() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");
		$this->setMailAttachments(new gevCrsMailAttachments($this->obj_id));
	}

	protected function initAutoMails() {
		require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMails.php");
		$this->setAutoMails(new gevCrsAutoMails($this->obj_id));
	}

	protected function initMailLog() {
		require_once("Services/Mailing/classes/class.ilMailLog.php");

		if ($this->mail_log === null) {
			$this->mail_log = new ilMailLog($this->obj_id);
		}
	}

	protected function initInvitationMailSettings() {
		$this->setInvitationMailSettings(new gevCrsInvitationMailSettings($this->obj_id));
	}

	protected function initAdditionalMailSettings() {
		$this->setAdditionalMailSettings(new gevCrsAdditionalMailSettings($this->obj_id));
	}

	protected $invitation_mail_settings;

	protected function setInvitationMailSettings(gevCrsInvitationMailSettings $a_settings) {
		$this->invitation_mail_settings = $a_settings;
	}

	protected function getInvitationMailSettings() {
		if ($this->invitation_mail_settings === null) {
			$this->initInvitationMailSettings();

			if ($this->invitation_mail_settings == null) {
				throw new Exception("Member invitation_mail_settings still ".
									"unitialized after call to initInvitationMailSettings. ".
									" Did you forget to call setInvitationMailSettings in ".
									"you implementation of initInvitationMailSettings?");
			}
		}

		return $this->invitation_mail_settings;
	}


	protected $additional_mail_settings;

	protected function setAdditionalMailSettings(gevCrsAdditionalMailSettings $a_settings) {
		$this->additional_mail_settings = $a_settings;
	}

	protected function getAdditionalMailSettings() {
		if ($this->additional_mail_settings === null) {
			$this->initAdditionalMailSettings();

			if ($this->additional_mail_settings == null) {
				throw new Exception("Member additional_mail_settings still ".
									"unitialized after call to initAdditionalMailSettings. ".
									" Did you forget to call setAdditionalMailSettings in ".
									"you implementation of initAdditionalMailSettings?");
			}
		}

		return $this->additional_mail_settings;
	}

	protected $crs;

	public function __construct($a_obj_id, $a_ref_id, $a_parent_gui) {
		parent::__construct($a_obj_id, $a_ref_id, $a_parent_gui);

		global $ilAccess, $ilUser;

		$this->access = &$ilAccess;
		$this->user = &$ilUser;

		$this->invitation_mail_settings = null;
		$this->crs = null;
		$this->crs_utils = null;
	}

	protected function getCourse() {
		if ($this->crs === null) {
			$this->crs = new ilObjCourse($this->obj_id, false);
		}

		return $this->crs;
	}
	
	protected function getCourseUtils() {
		if ($this->crs_utils === null) {
			$this->crs_utils = &gevCourseUtils::getInstance($this->obj_id);
		}
		
		return $this->crs_utils;
	}

	protected function executeCustomCommand($a_cmd) {
		switch($a_cmd) {
			case "showInvitationMails":
			case "updateInvitationMails":
			case "confirmRefreshAttachments":
			case "refreshAttachments":
			case "previewInvitationMail":
			
			case "showAdditionalSettings":
			case "updateAdditionalSettings":
			
				$this->$a_cmd();
				break;
			default:
				die("Unknown command: ".$a_cmd);
		}
	}

	protected function setSubTabs() {
		// add sub tab for invitation mails here
		if($this->invitationMailTabVisible()) {
			$this->tabs->addSubTab( "invitationMails"
								  , $this->lng->txt("gev_crs_settings_invitation")
								  , $this->ctrl->getLinkTarget($this, "showInvitationMails")
								  );
		}

		parent::setSubTabs();
		
		if ($this->additionalSettingsTabVisible()) {
			$this->tabs->addSubTab("additionalSettings"
								  , $this->lng->txt("gev_mailing_additional_settings")
								  , $this->ctrl->getLinkTarget($this, "showAdditionalSettings")
								  );
		}
	}

	protected function activateCustomSubTab($a_cmd) {
		switch($a_cmd) {
			case "showInvitationMails":
			case "updateInvitationMails":
			case "previewInvitationMail":
				$this->tabs->setSubTabActive("invitationMails");
				break;

			case "showAdditionalSettings":
			case "updateAdditionalSettings":
				$this->tabs->setSubTabActive("additionalSettings");
				break;

			case "confirmRefreshAttachments":
			case "refreshAttachments":
				$this->tabs->setSubTabActive("attachments");
				break;
			default:
				throw new Exception("Unknown command: ".$a_cmd);
		}
	}
	
	protected function showOverrideAttachmentConfirmation($a_file) {
		if ($this->getMailAttachments()->isAutogeneratedFile($a_file["name"])) {
			ilUtil::sendFailure(sprintf($this->lng->txt("gev_cant_replace_autogenerated_file"), $a_file));
			$this->showAttachments();
			return;
		}
		
		parent::showOverrideAttachmentConfirmation($a_file);
	}

	// INVITATION_MAILS

	protected function getFunctionsForInvitationMails() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

		$roles = gevCourseUtils::getCustomRoles($this->obj_id);
		$ret = array($this->lng->txt("crs_member"));
		
		foreach($roles as $role) {
			$ret[] = $role["title"];
		}

		return $ret;
	}

	protected function showInvitationMails() {
		$tpl = new ilTemplate("tpl.invitation_mail_settings.html", false, true, "Services/GEV/Mailing");

		$tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$tpl->setVariable("TXT_INVITATION_MAILS", $this->lng->txt("gev_crs_settings_invitation"));


		// Standardmail
		$mail_select = $this->getMailTemplateSelect("standard", $this->lng->txt("dont_send_mail"));
		$attachment_select = $this->getInvitationAttachmentSelect("standard");
		$tpl->setCurrentBlock("standard_mail");
		$tpl->setVariable("TXT_STANDARD_MAIL", $this->lng->txt("gev_standard_mail"));
		$tpl->setVariable("MAIL_SELECTION", $mail_select->render());
		$tpl->setVariable("ATTACHMENT_SELECTION", $attachment_select?$attachment_select->render():"");
		$this->ctrl->setParameter($this, "function", "standard");
		$tpl->setVariable("URL_PREVIEW", $this->ctrl->getLinkTarget($this, "previewInvitationMail"));
		$this->ctrl->clearParameters($this);
		$tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("mails_for_functions");
		$tpl->setVariable("TXT_FUNCTION", $this->lng->txt("gev_crs_function"));
		$tpl->setVariable("TXT_TEMPLATE", $this->lng->txt("gev_crs_mail_template"));
		$tpl->setVariable("TXT_ATTACHMENT", $this->lng->txt("mail_attachments"));

		// Mails for people with functions
		$functions = $this->getFunctionsForInvitationMails();

		$count = 0;

		foreach ($functions as $name) {
			$mail_select = $this->getMailTemplateSelect($name, $this->lng->txt("use_standard_mail"));
			$attachment_select = $this->getInvitationAttachmentSelect($name);

			$tpl->setCurrentBlock("row_bl");
			$tpl->setVariable("FUNCTION_NAME", $name);
			$tpl->setVariable("MAIL_SELECTION", $mail_select->render());
			if($attachment_select !== null AND $this->getInvitationMailSettings()->getTemplateFor($name) != -1) {
				$tpl->setVariable("ATTACHMENT_SELECTION", $attachment_select->render());
			}
			else {
				$tpl->setVariable("ATTACHMENT_SELECTION", "&nbsp;");
			}
			$this->ctrl->setParameter($this, "function", $name);
			$tpl->setVariable("URL_PREVIEW", $this->ctrl->getLinkTarget($this, "previewInvitationMail"));
			$this->ctrl->clearParameters($this);
			$tpl->setVariable("TXT_PREVIEW", $this->lng->txt("preview"));
			$tpl->setVariable("ROW_CLASS", ($count % 2 == 0?"tblrow1":"tblrow2"));
			$tpl->parseCurrentBlock();

			$count++;
		}

		$tpl->parseCurrentBlock();

		$this->tpl->setContent($tpl->get());
	}

	protected function previewInvitationMail() {
		$function = $_GET["function"];

		if (!$function) {
			$this->ctrl->redirect($this, "showInvitationMails");
		}

		require_once("Services/Mailing/classes/class.ilMailViewGUI.php");

		$mail = $this->getAutoMails()->getInvitationMailFor($function, $this->user->getId());

		if ($mail === null) {
			ilUtil::sendFailure($this->lng->txt("no_invitation_mail_available"));
			return $this->showInvitationMails();
		}

		$is_html_mail = strlen($mail["message_html"]) > 0;

		$view_gui=  new ilMailViewGUI( $this->lng->txt("preview").": "."Einladungsmail für ".$this->lng->txt($function)
									 , $this->ctrl->getLinkTarget($this, "showInvitationMails")
									 , $mail["subject"]
									 , $is_html_mail ? $mail["message_html"] : $mail["message_plain"]
									 , $is_html_mail ? $mail["frame_html"] : $mail["frame_plain"]
									 , $is_html_mail ? $mail["image_path"] : null
									 , $is_html_mail ? $mail["image_style"] : null
									 , $mail["attachments"]
									 );

		$this->tpl->setContent($view_gui->getHTML());
	}

	protected function updateInvitationMails() {
		$functions = $this->getFunctionsForInvitationMails();
		$functions[] = "standard";

		$success = true;

		foreach ($functions as $name) {
			if (!array_key_exists($name, $_POST)) {
				die("Settings for ".$name." not found in POST-data.");
			}

			$settings = $_POST[$name];

			if (!array_key_exists("template", $settings)) {
				die("No template set for ".$name.".");
			}
			if (!array_key_exists("attachments", $settings)
				// template = -1 is the standard mail, no extra attachments here.
				OR ($name != "standard" AND $settings["template"] == -1)) {
				$settings["attachments"] = array();
			}

			$this->getInvitationMailSettings()->setSettingsFor($name, $settings["template"], $settings["attachments"]);
		}

		if ($success) {
			$this->getInvitationMailSettings()->save();
			ilUtil::sendSuccess($this->lng->txt("gev_invitation_mail_settings_success"));
		}

		$this->showInvitationMails();
	}

	protected function getMailTemplateSelect($a_function_name, $a_default_option) {
		require_once("Services/Form/classes/class.ilSelectInputGUI.php");

		$select = new ilSelectInputGUI("", $a_function_name."[template]");
		$select->setOptions($this->getInvitationMailSettings()->getInvitationMailTemplates($a_default_option));
		$select->setValue($this->getInvitationMailSettings()->getTemplateFor($a_function_name));
		// TODO: Set current option
		return $select;
	}

	protected function getInvitationAttachmentSelect($a_function_name) {
		$select = $this->getAttachmentSelect();
		$select->setValue($this->getInvitationMailSettings()->getAttachmentNamesFor($a_function_name));
		$select->setTitle("");
		$select->setPostVar($a_function_name."[attachments]");
		$select->setHeight(75);
		$select->setWidth(160);
		return $select;
	}

	protected function showAttachmentRemoveFailure($a_filename) {
		$invMailFunctions = $this->getFunctionsForInvitationMails();
		$invMailFunctions[] = "standard";

		$functions = array();

		foreach($invMailFunctions as $function) {
			$att = $this->getInvitationMailSettings()->getAttachmentsFor($function);
			
			foreach($att as $attachment) {
				$this->ctrl->setParameter($this, "auto_mail_id", "participant_invitation");
				$this->ctrl->setParameter($this, "filename", $attachment["name"]);
				$link = $this->ctrl->getLinkTarget($this, "deliverAutoMailAttachment");
				$this->ctrl->clearParameters($this);
				$attachment["link"] = $link;
			}

			if (in_array($a_filename, $att)) {
				if ($function = "standard") {
					$functions[] = $this->lng->txt("gev_standard_mail");
				}
				else {
					$functions[] = sprintf($this->lng->txt("gev_invitation_mail_for"), $function);
				}
			}
		}

		ilUtil::sendFailure(sprintf($this->lng->txt("attachment_remove_failure"), $a_filename)." ".
							$this->lng->txt("gev_attachment_used_in").": <br />".
							implode($functions, "<br />"));
	}

	protected function showAttachments() {
		$res = parent::showAttachments();

		if ($this->getCourseUtils()->isTemplate()) {
			$this->toolbar->addSeparator();
			$this->toolbar->addFormButton($this->lng->txt("gev_refresh_attachments"), "confirmRefreshAttachments");
		}

		return $res;
	}

	// Refreshing of attachments at seminar templates.

	protected function confirmRefreshAttachments() {
		require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");

		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		$conf->setHeaderText($this->lng->txt("gev_confirm_refresh_attachments"));

		$conf->setConfirm($this->lng->txt("refresh"), "refreshAttachments");
		$conf->setCancel($this->lng->txt("cancel"), "showAttachments");

		require_once("./Services/GEV/Utils/classes/class.gevCourseUtils.php");

		foreach(gevCourseUtils::getInstance($this->obj_id)->getDerivedCourseIds() as $crs_id) {
			$util = gevCourseUtils::getInstance($crs_id);
			$conf->addItem("crs", $crs_id, $util->getTitle()." (".
							$util->getFormattedAppointment()
							.")");
		}

		$this->tpl->setContent($conf->getHTML());
	}

	protected function refreshAttachments() {
		require_once("./Services/GEV/Utils/classes/class.gevCourseUtils.php");
		foreach(gevCourseUtils::getInstance($this->obj_id)->getDerivedCourseIds() as $crs_id) {
			$this->getAttachments()->copyTo($crs_id);
		}

		ilUtil::sendSuccess($this->lng->txt("gev_attachments_refreshed"));
		return $this->showAttachments();
	}
	
	
	// ADDITIONAL SETTINGS
	
	protected function showAdditionalSettings($a_form = null) {
		if ($a_form === null) {
			$a_form = $this->getAdditionalSettingsForm();
		}

		$this->tpl->setContent($a_form->getHTML());
	}

	protected function updateAdditionalSettings() {
		$form = $this->getAdditionalSettingsForm();
		
		$form->setValuesByPost();
		if ($form->checkInput()) {
			$this->getAdditionalMailSettings()->setSendListToAccomodation((bool) ($form->getInput("send_list_to_accom") == 1));
			$this->getAdditionalMailSettings()->setSendListToVenue((bool) ($form->getInput("send_list_to_venue") == 1));
			$this->getAdditionalMailSettings()->setInvitationMailingDate(intval($form->getInput("inv_mailing_date")));
			$this->getAdditionalMailSettings()->setSuppressMails((bool) ($form->getInput("suppress_mails") == 1));
			$this->getAdditionalMailSettings()->save();
			
			$form->getItemByPostVar("suppress_mails")->setDisabled($this->getAdditionalMailSettings()->getSuppressMails());
			
			ilUtil::sendSuccess($this->lng->txt("gev_additional_settings_updated"));
			$this->getCourse()->update();
		}
		else {
			ilUtil::sendFailure($this->lng->txt("gev_additional_settings_update_failure"));
		}
		
		$this->showAdditionalSettings($form);
	}

	protected function getAdditionalSettingsForm() {
		require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		require_once("Services/Form/classes/class.ilNumberInputGUI.php");
		
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->addCommandButton("updateAdditionalSettings", $this->lng->txt("save"));
		
		$accom_mails = new ilFormSectionHeaderGUI();
		$accom_mails->setTitle("Teilnehmerlisten");
		$form->addItem($accom_mails);
		
		$send_list_to_accom = new ilCheckboxInputGUI();
		$send_list_to_accom->setTitle("Übernachtungsort");
		$send_list_to_accom->setPostvar("send_list_to_accom");
		$send_list_to_accom->setOptionTitle("Mails mit Teilnehmerlisten an Übernachtungsort automatisch versenden");
		$send_list_to_accom->setChecked($this->getAdditionalMailSettings()->getSendListToAccomodation());
		$form->addItem($send_list_to_accom);
		
		$send_list_to_venue = new ilCheckboxInputGUI();
		$send_list_to_venue->setTitle("Veranstaltungsort");
		$send_list_to_venue->setPostvar("send_list_to_venue");
		$send_list_to_venue->setOptionTitle("Mails mit Teilnehmerlisten an Veranstaltungsort automatisch versenden");
		$send_list_to_venue->setChecked($this->getAdditionalMailSettings()->getSendListToVenue());
		$form->addItem($send_list_to_venue);
		
		$mailing_dates = new ilFormSectionHeaderGUI();
		$mailing_dates->setTitle("Versanddaten");
		$form->addItem($mailing_dates);
		
		$inv_mailing_date = new ilNumberInputGUI();
		$inv_mailing_date->setTitle("Einladungsmail");
		$inv_mailing_date->setPostvar("inv_mailing_date");
		$inv_mailing_date->setMinValue(0);
		$inv_mailing_date->setDecimals(0);
		$inv_mailing_date->setInfo($this->lng->txt("gev_mailing_inv_mailing_date_expl"));
		$inv_mailing_date->setValue($this->getAdditionalMailSettings()->getInvitationMailingDate());
		$form->addItem($inv_mailing_date);
		
		$suppress_mails = new ilFormSectionHeaderGUI();
		$suppress_mails->setTitle("Mailversand");
		$form->addItem($suppress_mails);
		
		$suppress_mails = new ilCheckboxInputGUI();
		$suppress_mails->setTitle($this->lng->txt("gev_suppress_mails"));
		$suppress_mails->setPostvar("suppress_mails");
		$suppress_mails->setOptionTitle($this->lng->txt("gev_suppress_mails_info"));
		$suppress_mails->setChecked($this->getAdditionalMailSettings()->getSuppressMails());
		$suppress_mails->setDisabled($this->getAdditionalMailSettings()->getSuppressMails());
		
		$form->addItem($suppress_mails);
		
		return $form;
	}
}