<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantAbsentNotExcused extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Teilnahmestatus 'fehlt unentschuldigt'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F03";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseAbsentParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>