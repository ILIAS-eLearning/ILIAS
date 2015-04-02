<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevAdminBookingToBooked extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Admin bucht Teilnehmer";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B05";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseParticipants();
	}
	
	public function getCC($a_recipient) {
		return $this->maybeSuperiorsCC($a_recipient);
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
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}
		
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


		return parent::getMail($a_recipient);
	}


	public function getAttachmentsForMail() {
		require_once ("Services/GEV/Mailing/classes/class.gevCrsMailAttachments.php");

		$ical = gevCrsMailAttachments::ICAL_ENTRY;
		$path = $this->getAttachments()->getPathTo($ical);

		return array( array( "name" => $ical
						   , "path" => $path
						   )
					);
	}

}

?>