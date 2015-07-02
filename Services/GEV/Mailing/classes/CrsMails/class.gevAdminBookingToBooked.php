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