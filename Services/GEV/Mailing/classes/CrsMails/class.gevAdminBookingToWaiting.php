<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevAdminBookingToWaiting extends gevCrsAutoMail {
		const DAYS_BEFORE_COURSE_START = 14;
	
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Admin bucht Teilnehmer auf Warteliste";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B08";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseUsersOnWaitingList();
	}
	
	public function getCC($a_recipient) {
		return $this->maybeSuperiorsCC($a_recipient);
	}
}

?>