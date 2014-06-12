<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantAbsentExcused extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Teilnahmestatus 'fehlt entschuldigt'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F2";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseExcusedParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>