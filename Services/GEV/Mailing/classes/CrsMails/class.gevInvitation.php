<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");
require_once("Services/GEV/Mailing/classes/class.gevCrsAdditionalMailSettings.php");

class gevInvitation extends gevCrsAutoMail {
	protected $mail_settings;
	protected $attachements;
	
	const STOP_SEND_EXCEPTION_MESSAGE = "Stop sending invitation mail. No invitation mail defined.";

	public function __construct($a_crs_id, $a_id) {
		parent::__construct($a_crs_id, $a_id);

		$this->mail_settings = null;
		$this->attachments = null;
		
		
		$additional_mail_settings = new gevCrsAdditionalMailSettings($a_crs_id);
		$this->days_before_course_start = $additional_mail_settings->getInvitationMailingDate();
	}

	public function getTitle() {
		return "Einladung";
	}

	public function getDescription() {
		return $this->days_before_course_start." Tage vor Trainingsbeginn";
	}
	
	public function _getDescription() {
		return "";
	}
	
	public function getTemplateCategory() {
		return "";
	}

	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date) {
			$date->increment(IL_CAL_DAY, -1 * $this->days_before_course_start);
		}
		return $date;
	}

	public function getUsersOnly() {
		return true;
	}

	public function getRecipientUserIDs() {
		return array_merge($this->getCourseParticipants(), $this->getCourseSpecialMembers(),$this->getCourseTrainers());
	}

	public function getRecipientAddresses() {
		$ret = array();
		foreach ($this->getRecipientUserIDs() as $user_id) {
			$ret[] = ilObjUser::_lookupEmail($user_id);
		}
		return $ret;
	}

	public function getCC($a_recipient) {
		return array();
	}
	
	public function send($a_recipients = null, $a_occasion = null) {
		if ($a_recipients !== null) {
			// remove deferred mails for the people who receive this mail now (#1019)
			require_once("./Services/GEV/Mailing/classes/class.gevDeferredMails.php");
			gevDeferredMails::getInstance()
				->removeDeferredMails(array($this->crs_id), array($this->getId()), $a_recipients);
		}
		
		return parent::send($a_recipients, $a_occasion);
	}

	public function getMail($a_recipient) {
		require_once("Services/Context/classes/class.ilContext.php");
		require_once("Services/GEV/Mailing/classes/class.gevDeadlineMailingJob.php");
		
		if (!$this->checkUserID($a_recipient)) {
			throw new Exception("GEV-Invitation-Mails will only work for ILIAS-Users.");
		}
		
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}
		
		if ($this->getCourseUtils()->isTemplate()) {
			return null;
		}

		// this really is no good style.
		$start_date = $this->getCourseUtils()->getStartDate();
		$now = date("Y-m-d");
		if ($start_date && $start_date->get(IL_CAL_DATE) != $now) {
			if (   !gevDeadlineMailingJob::isMailSend($this->getCourse()->getId(), $this->getId()) 
				&& ilContext::getType() !== ilContext::CONTEXT_CRON 
				&& $_GET["cmdClass"] !== "ilcronmanagergui"
				&& $_GET["cmdClass"] !== "gevcrsmailinggui"
				&& $_GET["cmdClass"] !== "gevdecentraltraininggui"
				&& $_GET["cmdClass"] !== "gevtrainermailhandlinggui"
				&& $this->days_before_course_start != 0) {
				return null;
			}
		}

		$function = $this->getUserFunction($a_recipient);

		// function will be null if user is not member of the course. Fall back to
		// standard mail.
		if ($function === null) {
			$function = "standard";
		}

		return $this->getMailFor($function, $a_recipient);
	}

	public function getMailFor($a_function, $a_recipient) {
		require_once("./Services/GEV/Mailing/classes/class.gevCrsInvitationMailSettings.php");
		
		if ($this->mail_settings === null) {
			$this->mail_settings = new gevCrsInvitationMailSettings($this->crs_id);
		}

		$template_id = $this->mail_settings->getTemplateFor($a_function);
		
		// This is the key for the "No Mail"-option.
		if($template_id == -2) { return null; }
		
		// This is the key for the "Standard-Mail"-option
		if($template_id == -1) {
			$template_id = $this->mail_settings->getTemplateFor("standard");
			$attachments = $this->mail_settings->getAttachmentsFor("standard");
		}
		else {
			$attachments = $this->mail_settings->getAttachmentsFor($a_function);
		}

		// Just stop sending if theres no mail defined.
		if ($template_id == -1) {
			global $ilLog;
			$ilLog->write('! sending stopped; there is no template (recipient:' .$a_recipient .')');
			return null;
		}
		

		$message = $this->getMessageFromTemplate($template_id
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
					, "attachments" => $attachments
					, "frame_plain" => $this->template_frame->getPlainTextFrame()
					, "frame_html" => $this->template_frame->getHtmlFrame()
					, "image_path" => $this->template_frame->getFileSystemBasePath()."/"
									  .$this->template_frame->getImageName()
					, "image_styles" => $this->template_frame->getImageStyles()
					);
	}

	public function getAttachmentPath($a_name) {
		if (!$this->getAttachments()->isAttachment($a_name)) {
			throw new Exception("'".$a_name."' is no attachment of participant invitations.");
		}

		return $this->getAttachments()->getPathTo($a_name);
	}
}

?>