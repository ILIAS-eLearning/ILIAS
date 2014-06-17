<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevListForAccomodation extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 14;
	
	public function getTitle() {
		return "Teilnehmerliste Hotel";
	}
	
	public function _getDescription() {
		return self::DAYS_BEFORE_COURSE_START." Tage vor Trainingsbeginn";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseStart();
		if ($date) {
			$date->increment(-1 * self::DAYS_BEFORE_COURSE_START, IL_CAL_DAY);
		}
		return $date;
	}
	
	public function getTemplateCategory() {
		return "R04";
	}
	
	public function getUsersOnly() {
		return false;
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseHotelAddresses();
	}
	
	protected function getFullnameForTemplate($a_recipient) {
		return $a_recipient["name"];
	}
	
	protected function getEmailForTemplate($a_recipient) {
		return $a_recipient["email"];
	}
	
	public function getMessageFromTemplate($a_template_id, $a_recipient, $a_fullname, $a_email) {
		return parent::getMessageFromTemplate($a_template_id, null, $a_fullname, $a_email);
	}
	
	public function getMail($a_recipient) {
		if ($this->checkUserID($a_recipient)) {
			$a_recipient = array( "name" => ilObjUser::_lookupFullname($a_recipient)
								, "email" => ilObjUser::_lookupEmail($a_recipient));
		}
		
		$message = $this->getMessageFromTemplate($this->getTemplateId()
												, null
												, null
												, null);

		return array( "from" => $this->getFrom()
					, "to" => $a_recipient["name"]." <".$a_recipient["email"].">"
					, "cc" => $this->getCC($a_recipient)
					, "bcc" => $this->getBCC($a_recipient)
					, "subject" => $message["subject"]
					, "message_plain" => $message["plain"]
					, "message_html" => $message["html"]
					, "attachments" => $this->getAttachmentsForMail($a_recipient)
					);
	}
	
	public function getAttachmentsForMail() {
		require_once ("Services/VoFue/Course/classes/class.gevCrsMailAttachments.php");

		$member_list_name = gevCrsMailAttachments::LIST_FOR_HOTEL_NAME;
		$path = $this->getAttachments()->getPathTo($member_list_name);

		$this->ctrl->setParameterByClass("vfCrsMailingGUI", "auto_mail_id",$this->getId());
		$this->ctrl->setParameterByClass("vfCrsMailingGUI", "filename", $member_list_name);
		$link = $this->ctrl->getLinkTargetByClass("vfCrsMailingGUI", "deliverAutoMailAttachment");
		$this->ctrl->clearParametersByClass("vfCrsMailingGUI");

		$message = $this->getMessageFromTemplate($this->getTemplateId(), null, $a_recipient["email"], $a_recipient["name"]);

		return array( array( "name" => $member_list_name
						   , "path" => $path
						   , "link" => $link
						   )
					);
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>