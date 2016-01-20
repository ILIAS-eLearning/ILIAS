<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevReminderWebinarAutoMail
*
* @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
*/

require_once ("./Services/Mailing/classes/class.ilAutoMail.php");
require_once ("./Services/GEV/Utils/classes/class.gevCourseUtils.php");

abstract class gevWebinarAutoMail extends ilAutoMail {

	private static $template_type = "WebinarMail";

	public function __construct($a_id, $crs_id) {
		global $ilDB, $ilCtrl, $ilias, $ilSetting, $ilUser;

		$this->gDb = $ilDB;
		$this->gSettings = $ilSetting;
		$this->gIlias = $ilias;

		$this->template_api = null;
		$this->template_settings = null;
		$this->template_variant = null;
		$this->mail_log = null;
		$this->global_bcc = null;
		$this->crs_id = $crs_id;
		$this->crs_utils = null;

		parent::__construct($a_id);
	}

	// TODO: Move this to ilAutoMail
	public function getLastSend() {
		return null;
	}

	public function getDescription() {
		return $this->_getDescription().", Vorlage ".$this->getTemplateCategory();
	}

	abstract function _getDescription();

	// SOME DEFAULTS

	public function getUsersOnly() {
		return true;
	}

	public function getRecipientUserIDs() {
		return array();
	}

	public function getRecipientAddresses() {
		$ret = array();
		foreach ($this->getRecipientUserIDs() as $user_id) {
			$ret[] = ilObjUser::_lookupEmail($user_id);
		}
		return $ret;
	}

	protected function checkUserID($a_recipient) {
		return is_numeric($a_recipient) && ilObjUser::_lookupEmail($a_recipient) !== false;
	}

	public function initTemplateObjects($a_templ_id, $a_language) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateFrameSettingsEntity.php";
		
		if ($this->template_api === null) {
			$this->template_api = new ilMailTemplateManagementAPI();
		}
		if ($this->template_settings === null) {
			$this->template_settings = new ilMailTemplateSettingsEntity();
			$this->template_settings->setIlDB($this->gDb);
		}
		if($this->template_variant === null) {
			$this->template_variant = new ilMailTemplateVariantEntity();
			$this->template_variant->setIlDB($this->gDb);
		}
		if ($this->template_frame === null) {
			$this->template_frame = new ilMailTemplateFrameSettingsEntity($this->gDb, new ilSetting("mail_tpl"));
		}

		$this->template_settings->loadById($a_templ_id);
		$this->template_variant->loadByTypeAndLanguage($a_templ_id, $a_language);
	}

	protected function getTemplateIdByTypeAndCategory($a_type, $a_category) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";
		if ($this->template_settings === null) {
			$this->template_settings = new ilMailTemplateSettingsEntity();
			$this->template_settings->setIlDB($this->gDb);
		}

		$this->template_settings->loadByCategoryAndTemplate($a_category, $a_type);
		return $this->template_settings->getTemplateTypeId();
	}

	protected function getFrom() {
		$fn = $this->gSettings->get("mail_system_sender_name");
		$fm = $this->gIlias->getSetting("mail_external_sender_noreply");

		return $fn." <".$fm.">";
	}

	protected function getTo($a_user_id) {
		$tn = ilObjUser::_lookupFullname($a_user_id);
		$tm = ilObjUser::_lookupEmail($a_user_id);

		return $tn." <".$tm.">";
	}

	protected function getCC($a_recipient) {
		return array();
	}

	protected function getBCC($a_recipient) {
		return array();
	}

	protected function getFullnameForTemplate($a_recipient) {
		return ilObjUser::_lookupFullname($a_recipient);
	}

	protected function getEmailForTemplate($a_recipient) {
		return ilObjUser::_lookupEmail($a_recipient);
	}

	protected function getNameForTemplate($a_recipient) {
		return ilObjUser::_lookupName($a_recipient);
	}

	protected function getGenderForTemplate($a_recipient) {
		return ilObjUser::_lookupGender($a_recipient);
	}

	public function getAttachmentPath($a_name) {
		return "";
	}

	protected function getAttachmentsForMail($a_recipient) {
		return array();
	}

	protected function getMessage($a_template_id, $a_recipient) {
		$message = $this->getMessageFromTemplate($a_template_id
												, $a_recipient["email"]
												);

		return array( "from" => $this->getFrom()
					, "to" =>$a_recipient["name"]." <".$a_recipient["email"].">"
					, "cc" => array()
					, "bcc" => $this->getBCC($a_recipient)
					, "subject" => $message["subject"]?$message["subject"]:""
					, "message_plain" => str_replace("<br />", "\n", $message["plain"])
					, "message_html" => $message["html"]
					, "attachments" => array()
					, "frame_plain" => $this->template_frame->getPlainTextFrame()
					, "frame_html" => $this->template_frame->getHtmlFrame()
					, "image_path" => $this->template_frame->getFileSystemBasePath()."/"
									  .$this->template_frame->getImageName()
					, "image_styles" => $this->template_frame->getImageStyles()
					);
	}

	// Turn template to mail content. Returns
	// a dict containing fields "subject", "plain" and "html"
	protected function getMessageFromTemplate($a_templ_id, $a_recipient) {
		$this->initTemplateObjects($a_templ_id, "de");
		$rec_gender = $this->getGenderForTemplate($a_recipient);

		require_once "./Services/GEV/Mailing/classes/class.gevWebinarMailData.php";
		$mail_data = new gevWebinarMailData($a_recipient,$rec_gender);
		$mail_data->initCourseData($this->getCourseUtils());

		$adapter = $this->template_settings->getAdapterClassInstance();

		$placeholders = $adapter->getPlaceholdersLocalized();
		return $this->template_api->getPopulatedVariantMessages($this->template_variant
															   , $placeholders
															   , $mail_data
															   , "de");
	}

	public function getMail($a_recipient) {
		return $this->getMessage($this->getTemplateId(), $a_recipient);
	}

	public function getTemplateId() {
		return $this->getTemplateIdByTypeAndCategory($this->getTemplateType(), $this->getTemplateCategory());
	}

	public function getTemplateType() {
		return self::$template_type;
	}

	abstract public function getTemplateCategory();

	protected function getCourseStart() {
		return $this->getCourseUtils()->getStartDate();
	}

	protected function getCourseStartWithTime() {
		$start_date = $this->getCourseUtils()->getStartDate();
		if(!$start_date) {
			return false;
		}
		
		return new DateTime($this->getCourseUtils()->getStartDate()->get(IL_CAL_DATE)." ".$this->getCourseUtils()->getFormattedStartTime().":00");
	}

	protected function getCourseUtils() {
		if(!$this->crs_utils) {
			$this->crs_utils = gevCourseUtils::getInstance($this->crs_id);
		}

		return $this->crs_utils;
	}

	public function getCourseIsStarted() {
		return $this->getCourseUtils()->isStarted();
	}
}