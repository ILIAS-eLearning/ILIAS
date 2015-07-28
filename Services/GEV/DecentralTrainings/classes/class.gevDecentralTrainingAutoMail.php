<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevCrsAutoMail
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once ("./Services/Mailing/classes/class.ilAutoMail.php");
require_once ("./Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once ("./Services/GEV/Utils/classes/class.gevUserUtils.php");

abstract class gevDecentralTrainingAutoMail extends ilAutoMail {
	protected $template_api;
	protected $template_settings;
	protected $template_variant;
	protected $mail_log;
	protected $global_bcc;
	
	private static $template_type = "DecentralTrainingCreation";

	public function __construct(gevDecentralTrainingCreationRequest $a_request, $a_id) {
		global $ilDB, $lng, $ilCtrl, $ilias, $ilSetting, $ilUser;

		$this->db = &$ilDB;
		$this->lng = &$lng;
		$this->settings = &$ilSetting;
		$this->ilias = &$ilias;
		$this->request = $a_request;

		$this->template_api = null;
		$this->template_settings = null;
		$this->template_variant = null;
		$this->mail_log = null;
		$this->global_bcc = null;

		parent::__construct($a_id);
	}
	
	public function getTitle() {
		return "Info Anlage dezentrale Trainings";
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

	public function getScheduledFor() {
		return null;
	}

	public function getUsersOnly() {
		return true;
	}

	public function getRecipientUserIDs() {
		return array($this->request->userId());
	}

	public function getRecipientAddresses() {
		return array();
	}

	public function getAttachmentPath($a_name) {
		throw new Exception("gevRegistrationMails::getAttachmentPath: Attachments are not supported by Registration Mails.");
	}


	private function initTemplateObjects($a_templ_id, $a_language) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateFrameSettingsEntity.php";
		
		if ($this->template_api === null) {
			$this->template_api = new ilMailTemplateManagementAPI();
		}
		if ($this->template_settings === null) {
			$this->template_settings = new ilMailTemplateSettingsEntity();
			$this->template_settings->setIlDB($this->db);
		}
		if($this->template_variant === null) {
			$this->template_variant = new ilMailTemplateVariantEntity();
			$this->template_variant->setIlDB($this->db);
		}
		if ($this->template_frame === null) {
			$this->template_frame = new ilMailTemplateFrameSettingsEntity($this->db, new ilSetting("mail_tpl"));
		}

		$this->template_settings->loadById($a_templ_id);
		$this->template_variant->loadByTypeAndLanguage($a_templ_id, $a_language);
	}

	protected function getTemplateIdByTypeAndCategory($a_type, $a_category) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";
		if ($this->template_settings === null) {
			$this->template_settings = new ilMailTemplateSettingsEntity();
			$this->template_settings->setIlDB($this->db);
		}

		$this->template_settings->loadByCategoryAndTemplate($a_category, $a_type);
		return $this->template_settings->getTemplateTypeId();
	}

	protected function getFrom() {
		$fn = $this->settings->get("mail_system_sender_name");
		$fm = $this->ilias->getSetting("mail_external_sender_noreply");

		return $fn." <".$fm.">";
	}

	protected function getBCC($a_recipient) {
		return array();
	}

	protected function getAttachmentsForMail($a_recipient) {
		return array();
	}
	
	protected function getTo($a_user_id) {
		$tn = ilObjUser::_lookupFullname($a_user_id);
		$tm = ilObjUser::_lookupEmail($a_user_id);

		return $tn." <".$tm.">";
	}
	
	protected function getMessage($a_template_id, $a_recipient) {
		$message = $this->getMessageFromTemplate( $a_template_id
												, $a_recipient
												);

		return array( "from" => $this->getFrom()
					, "to" => $this->getTo($a_recipient)
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
	protected function getMessageFromTemplate($a_templ_id, $a_recipient_id) {
		$this->initTemplateObjects($a_templ_id, "de");

		require_once "./Services/GEV/DecentralTrainings/classes/class.gevDecentralTrainingMailData.php";

		$mail_data = new gevDecentralTrainingMailData($this->request);
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
}

?>