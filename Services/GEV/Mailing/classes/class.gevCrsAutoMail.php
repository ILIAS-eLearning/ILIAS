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

abstract class gevCrsAutoMail extends ilAutoMail {
	protected $crs_id;
	protected $crs;
	protected $vofue_settings;
	protected $template_api;
	protected $template_settings;
	protected $template_variant;
	protected $mail_log;
	protected $global_bcc;

	protected $gev_crs_mail_template_type;
	
	private static $template_type = "CrsMail";

	public function __construct($a_crs_id, $a_id) {
		global $ilDB, $lng, $ilCtrl, $ilias, $ilSetting;

		$this->db = &$ilDB;
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->settings = &$ilSetting;
		$this->ilias = &$ilias;

		if (!is_numeric($a_crs_id)) {
			throw new Exception ("gevCrsAutoMail not initialised with integer crs_id.");
		}

		$this->crs_id = $a_crs_id;
		$this->crs = null;
		$this->crs_utils = null;

		$this->template_api = null;
		$this->template_settings = null;
		$this->template_variant = null;
		$this->mail_log = null;
		$this->gev_crs_mail_template_type = self::$template_type;
		$this->global_bcc = null;

		parent::__construct($a_id);
	}

	// TODO: Move this to ilAutoMail
	public function getLastSend() {
		$result = $this->db->query("SELECT last_send
									FROM gev_automail_info
									WHERE crs_id = ".$this->db->quote($this->crs_id)."
									AND mail_id = ".$this->db->quote($this->getId()));

		if ($record = $this->db->fetchAssoc($result)) {
			return new ilDateTime($record["last_send"], IL_CAL_UNIX);
		}

		return null;
	}

	protected function setLastSend() {
		$this->db->manipulate("INSERT INTO gev_automail_info (crs_id, mail_id, last_send)
							  VALUES ("
							  	.$this->db->quote($this->crs_id, "integer").", "
							  	.$this->db->quote($this->getId(), "text").", "
							  	.$this->db->quote(time(), "integer")."
							  ) ON DUPLICATE KEY UPDATE last_send = ".$this->db->quote(time(), "integer")
							);
	}

	protected function getCourse() {
		if ($this->crs === null) {
			$this->crs = new ilObjCourse($this->crs_id, false);
		}

		return $this->crs;
	}

	protected function getCourseUtils() {
		if ($this->crs_utils === null) {
			$this->crs_utils = gevCourseUtils::getInstance($this->crs_id);
		}
		return $this->crs_utils;
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
		return array();
	}

	public function getRecipientAddresses() {
		$ret = array();
		foreach ($this->getRecipientUserIDs() as $user_id) {
			$ret[] = ilObjUser::_lookupEmail($user_id);
		}
		return $ret;
	}

	// COURSE TIMES

	protected function getCourseBookingDeadline() {
		return $this->getCourseUtils()->getBookingDeadlineDate();
	}

	protected function getCourseCancelDeadline() {
		return $this->getCourseUtils()->getCancelDeadlineDate();
	}

	protected function getCourseStart() {
		return $this->getCourseUtils()->getStartDate();
	}

	protected function getCourseEnd() {
		return $this->getCourseUtils()->getEndDate();
	}

	// COURSE PEOPLE

	protected function getCourseParticipants() {
		return array();
		// TODO: maybe this needs to be adjusted to fit new gev logic
		$members = $this->getCourseMembers();
		$special_members = $this->getCourseSpecialMembers();
		$trainers = $this->getCourseTrainers();
		$admins = $this->getCourseAdmins();
		return array_diff($members, $special_members, $trainers, $admins);
	}

	protected function getCourseMembers() {
		// TODO: maybe this needs to be adjusted to fit new gev logic
		return $this->getCourse()->getMembersObject()->getParticipants();
	}

	protected function getCourseTrainers() {
		// TODO: maybe this needs to be adjusted to fit new gev logic
		return $this->getCourse()->getMembersObject()->getTutors();
	}

	protected function getCourseFullTrainers() {
		return array();
		// TODO: maybe this needs to be adjusted to fit new gev logic
		$full_ids = array();

		foreach ($this->getCourseTrainers() as $user_id) {
			$part = new vfParticipant($this->crs_id, $user_id);
			if ($part->getFunction() != vfParticipant::FUNCTION_TRAINER_SIDE) {
				$full_ids[] = $user_id;
			}
		}

		return $full_ids;
	}

	protected function getCourseSideTrainers() {
		return array();
		// TODO: maybe this needs to be adjusted to fit new gev logic
		$side_ids = array();

		foreach($this->getCourseMembers() as $user_id) {
			$part = new vfParticipant($this->crs_id, $user_id);
			if($part->getFunction() == vfParticipant::FUNCTION_TRAINER_SIDE) {
				$side_ids[] = $user_id;
			}
		}

		return $side_ids;
	}

	protected function getCourseSpecialMembers() {
		// TODO: this needs to be adjusted to fit new gev logic for sure
		return array();
		$co_ids = array();

		$co_functions = array( vfParticipant::FUNCTION_TRAINEE
							 , vfParticipant::FUNCTION_SPEAKER
							 , vfParticipant::FUNCTION_HOST
							 , vfParticipant::FUNCTION_ASSISTANT
							 , vfParticipant::FUNCTION_OBSERVER
							 , vfParticipant::FUNCTION_OBSERVER2
							 , vfParticipant::FUNCTION_OBSERVER3
							 , vfParticipant::FUNCTION_OBSERVER4
							 , vfParticipant::FUNCTION_GUEST
							 );

		foreach($this->getCourseMembers() as $user_id) {
			$part = new vfParticipant($this->crs_id, $user_id);
			if(in_array($part->getFunction(), $co_functions)) {
				$co_ids[] = $user_id;
			}
		}

		return $co_ids;
	}

	protected function getCourseAdmins() {
		return $this->getCourse()->getMembersObject()->getAdmins();
	}

	protected function getCourseAccomodationAddress() {
		$accom = $this->getCourseUtils()->getVenue();
		
		if ($accom === null ) {
			return null;
		}

		$email = $accom->getContactEmail();

		return array( "name" => $accom->getContactName()
					, "email" => $email
					);
	}

	protected function getCourseVenueAddress() {
		$ven = $this->getCourseUtils()->getVenue();
		
		if ($ven === null ) {
			return null;
		}

		$email = $ven->getContactEmail();

		return array( "name" => $ven->getContactName()
					, "email" => $email
					);
	}

	protected function getCourseHotelAddresses() {
		// TODO: this needs to be adjusted
		$to_accom = $this->getAdditionalMailSettings()->getSendListToAccomodation();
		$to_venue = $this->getAdditionalMailSettings()->getSendListToVenue();
		$accom_address = $this->getCourseAccomodationAddress();
		$venue_address = $this->getCourseVenueAddress();
		$addresses = array();

		if ($to_accom and $accom_address !== null) {
			$addresses[] = $accom_address;
		}
		if($to_venue and $venue_address !== null) {
			$addresses[] = $venue_address;
		}

		if( count($addresses) == 2
		and $addresses[0]["name"] == $addresses[1]["name"]
		and $addresses[0]["email"] == $addresses[1]["email"] ) {
			array_pop($addresses);
		}

		return $addresses;
	}

	protected function getCourseCancelledMembers() {
		return array();
		// TODO: this needs to be adjusted
		require_once("./Services/VoFue/Patch/classes/class.vfHistorizingHandler.php");

		$ret = array();

		foreach (vfHistorizingHandler::getHistorizedDeletedMembers($this->crs_id, array()) as $record) {
			$ret[] = $record["user_id"];
		}

		return $ret;
	}

	protected function getCourseCancelledWithCostsMembers() {
		return array();
		// TODO: this needs to be adjusted
		require_once("./Services/VoFue/Patch/classes/class.vfHistorizingHandler.php");

		$ret = array();

		foreach (vfHistorizingHandler::getHistorizedDeletedMembers($this->crs_id, array()) as $record) {
			$ret[] = $record["user_id"];
		}

		return $ret;
	}

	protected function getCourseCancelledWithoutCostsMembers() {
		return array();
		// TODO: this needs to be adjusted
		require_once("./Services/VoFue/Patch/classes/class.vfHistorizingHandler.php");

		$ret = array();

		foreach (vfHistorizingHandler::getHistorizedDeletedMembers($this->crs_id, array()) as $record) {
			$ret[] = $record["user_id"];
		}

		return $ret;
	}

	protected function getCourseSuccessfullParticipants() {
		return array();
		// TODO: this needs to be adjusted
		return $this->getCourseParticipantsWithStatus(vfParticipant::STATUS_ATTENDED);
	}

	protected function getCourseAbsentParticipants(){
		return array();
		// TODO: this needs to be adjusted
		return $this->getCourseParticipantsWithStatus(vfParticipant::STATUS_ABSENT_NO_INFO);
	}

	protected function getCourseExcusedParticipants(){
		return array();
		// TODO: this needs to be adjusted
		return array_merge( $this->getCourseParticipantsWithStatus(vfParticipant::STATUS_EXCUSED)
						  , $this->getCourseParticipantsWithStatus(vfParticipant::STATUS_REFUSED));
	}

	protected function getCourseParticipantsWithStatus($a_status) {
		return array();
		// TODO: this needs to be adjusted
		$ret = array();
		foreach ($this->getCourseParticipants() as $part_id) {
			$part = new vfParticipant($this->crs_id, $part_id);
			if ($part->getStatus() == $a_status) {
				$ret[] = $part_id;
			}
		}
		return $ret;
	}

	protected function filterUsersWithUnfullfilledParticipationPrecondition($a_user_ids) {
		// TODO: this needs to be adjusted
		$result = $this->db->query("SELECT precond_part
									 FROM vf_crs_data
									 WHERE id = ".$this->db->quote($this->crs_id, "integer")
								   );

		$row = $this->db->fetchAssoc($result);

		if($row["precond_part"]) {
			require_once "Services/Tracking/classes/class.ilLPStatus.php";

			$failed_members = array();

			$precond_id = ilObject::_lookupObjId($row["precond_part"]);

			foreach ($a_user_ids as $participant) {
				if (ilLPStatus::_lookupStatus($precond_id, $participant) != LP_STATUS_COMPLETED_NUM) {
					$failed_members[] = $participant;
				}
			}

			return $failed_members;
		}
		else {
			return array();
		}
	}

	protected function getCourseUsersOnWaitingList() {
		require_once("./Modules/Course/classes/class.ilCourseWaitingList.php");
		$wlist = new ilCourseWaitingList($this->crs_id);
		return $wlist->getUserIds();
	}

	protected function getAttachments() {
		if ($this->attachments === null) {
			require_once ("Services/VoFue/Course/classes/class.vfCrsMailAttachments.php");
			$this->attachments = new vfCrsMailAttachments($this->crs_id);
		}

		return $this->attachments;
	}

	public function getAttachmentPath($a_name) {
		return $this->getAttachments()->pathTo($a_name);
	}

	protected function checkUserID($a_recipient) {
		return is_numeric($a_recipient) && ilObjUser::_lookupEmail($a_recipient) !== false;
	}


	private function initTemplateObjects($a_templ_id, $a_language) {
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateSettingsEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateVariantEntity.php";
		require_once "./Services/MailTemplates/classes/class.ilMailTemplateManagementAPI.php";

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

	protected function getUserFunction($a_user_id) {
		// TODO: this needs to be adjusted
		$ref_ids = ilObject::_getAllReferences($this->crs_id);
		$ref_id = array_pop($ref_ids);
		return vfParticipant::getParsedFunction($ref_id, $user_id, true);
	}

	protected function getFrom() {
		$fn = $this->settings->get("mail_system_sender_name");
		$fm = $this->ilias->getSetting("mail_external_sender_noreply");

		return $fn." <".$fm.">";
	}


	protected function getTo($a_user_id) {
		$tn = ilObjUser::_lookupFullname($a_user_id);
		$tm = ilObjUser::_lookupEmail($a_user_id);

		return $tn." <".$tm.">";
	}

	protected function getCC($a_recipient) {
		//TODO: this needs to be adjusted
		require_once("Services/VoFue/Patch/classes/class.vfUtil.php");

		if (!$this->checkUserID($a_recipient)) {
			throw new Exception("VoFue-Mails will only work for ILIAS-Users.");
		}

		$superior_ids = vfUtil::getSuperior($a_recipient);

		return array_map(array($this, "getTo"), $superior_ids);
	}

	protected function getBCC($a_recipient) {
		//TODO: this needs to be adjusted
		if ($this->global_bcc === null) {
			require_once "Services/Administration/classes/class.ilSetting.php";
			$vofue_set = new ilSetting("vofue");
			$this->global_bcc = array($vofue_set->get("mail_setting_bcc"));
		}

		return $this->global_bcc;
	}

	protected function getFullnameForTemplate($a_recipient) {
		return ilObjUser::_lookupFullname($a_recipient);
	}

	protected function getEmailForTemplate($a_recipient) {
		return ilObjUser::_lookupEmail($a_recipient);
	}

	protected function getAttachmentsForMail($a_recipient) {
		return array();
	}

	protected function getMessage($a_template_id, $a_recipient) {
		$message = $this->getMessageFromTemplate($a_template_id
												, $a_recipient
												, $this->getFullnameForTemplate($a_recipient)
												, $this->getEmailForTemplate($a_recipient));

		return array( "from" => $this->getFrom()
					, "to" => $this->getTo($a_recipient)
					, "cc" => $this->getCC($a_recipient)
					, "bcc" => $this->getBCC($a_recipient)
					, "subject" => $message["subject"]
					, "message_plain" => $message["plain"]
					, "message_html" => $message["html"]
					, "attachments" => $this->getAttachmentsForMail($a_recipient)
					);
	}

	// Turn template to mail content. Returns
	// a dict containing fields "subject", "plain" and "html"
	protected function getMessageFromTemplate($a_templ_id, $a_user_id, $a_email, $a_name) {
		//TODO: this needs to be adjusted
		$this->initTemplateObjects($a_templ_id, "de");

		require_once "./Services/GEV/Mailing/classes/class.gevCrsMailData.php";

		$mail_data = new gevCrsMailData();
		$mail_data->initCourseData($this->getCourseUtils());

		if ($a_user_id !== null) {
			$mail_data->setRecipient($a_user_id, $a_email, $a_name);
			$mail_data->initUserData(gevUserUtils::getInstance($a_user_id));
		}

		$adapter = $this->template_settings->getAdapterClassInstance();

		$placeholders = $adapter->getPlaceholdersLocalized();
		return $this->template_api->getPopulatedVariantMessages($this->template_variant
															   , $placeholders
															   , $mail_data
															   , "de");
	}

	public function send($a_recipients = null, $a_occasion = null) {
		//TODO: this needs to be adjusted
		// Do not send mails for online-trainings.
		if ($this->getCourse()->getVfSettings()->isTypeOnline()) {
			return;
		}

		// Do not send mails for courses that are offline.
		if ($this->getCourse()->getOfflineStatus()) {
			return;
		}

		$res = parent::send($a_recipients, $a_occasion);
		if ($res) {
			$this->setLastSend();
		}
		return $res;
	}

	public function getMail($a_recipient) {
		if (!$this->checkUserID($a_recipient)) {
			throw new Exception("This mail will only work for ILIAS-Users.");
		}

		return $this->getMessage($this->getTemplateId(), $a_recipient);
	}

	public function getTemplateId() {
		return $this->getTemplateIdByTypeAndCategory($this->getTemplateType(), $this->getTemplateCategory());
	}

	public function getTemplateType() {
		return $this->gev_crs_mail_template_type;
	}

	abstract public function getTemplateCategory();

	public function swapToWithCC($a_message) {
		$cc = $a_message["cc"];

		if (count($cc) == 0) {
			// no cc means no superior => no need to send mail
			// VoFue #4934
			return null;
		}

		$cc_main = array_pop($cc);
		$a_message["cc"] = array_merge(array($a_message["to"]), $cc);
		$a_message["to"] = $cc_main;
		return $a_message;
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

	protected function initAdditionalMailSettings() {
		require_once ("./Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");
		$this->setAdditionalMailSettings(new gevCrsAdditionalMailSettings($this->crs_id));
	}

}

?>