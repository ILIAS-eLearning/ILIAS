<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevListForAccomodation extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 15;
	
	public function getTitle() {
		return "Teilnehmerliste Hotel";
	}
	
	public function _getDescription() {
		// Mail is send after the 15th day before training is over.
		// Thus we need to subtract, since after the 15th day is on the
		// 14th day.
		return (self::DAYS_BEFORE_COURSE_START -1)." Tage vor Trainingsbeginn";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseStart();
		if ($date) {
			$date->increment(IL_CAL_DAY, -1 * self::DAYS_BEFORE_COURSE_START);
		}
		return $date;
	}
	
	public function getTemplateCategory() {
		return "R04";
	}
	
	public function getUsersOnly() {
		return false;
	}
	
	public function getRecipientAddresses() {
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
		require_once ("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");

		$member_list_name = gevCrsMailAttachments::LIST_FOR_HOTEL_NAME;
		$path = $this->getAttachments()->getPathTo($member_list_name);

		$message = $this->getMessageFromTemplate($this->getTemplateId(), null, $a_recipient["email"], $a_recipient["name"]);

		return array( array( "name" => $member_list_name
						   , "path" => $path
						   )
					);
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>