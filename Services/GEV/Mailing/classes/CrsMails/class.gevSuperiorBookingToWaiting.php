<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSuperiorBookingToWaiting extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Buchungsstatus 'auf Warteliste' durch Buchung durch Führungskraft";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B04";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseUsersOnWaitingList();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>