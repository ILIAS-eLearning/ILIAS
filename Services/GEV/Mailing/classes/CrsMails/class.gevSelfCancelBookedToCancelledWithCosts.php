<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSelfCancelBookedToCancelledWithCosts extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer (gebucht) erhält Buchungsstatus 'kostenpflichtig storniert' durch Selbststornierung";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C3";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseCancelledWithCostsMembers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>