<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantWaitingToBooked extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer wechselt Buchungsstatus von 'auf Warteliste' zu 'gebucht'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B06";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
	
	public function getMail($a_recipient) {
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}
		
		return parent::getMail($a_recipient);
	}
}

?>