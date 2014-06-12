<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevAdminBookingToBooked extends gevCrsAutoMail {
	public function getTitle() {
		return "Info an Teilnehmer";
	}
	
	public function _getDescription() {
		return "Admin bucht Teilnehmer";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B5";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>