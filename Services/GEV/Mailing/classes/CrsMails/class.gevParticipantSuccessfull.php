<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantSuccessfull extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Teilnahmestatus 'teilgenommen'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F1";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseSuccessfullParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>