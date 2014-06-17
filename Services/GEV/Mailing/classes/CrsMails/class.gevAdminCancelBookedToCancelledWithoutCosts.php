<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevAdminCancelBookedToCancelledWithoutCosts extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer (gebucht) erhält Buchungsstatus 'kostenfrei storniert' durch Stornierung durch Führungskraft";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C04";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseCancelledWithoutCostsMembers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>