<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSuperiorCancelBookedToCancelledWithCosts extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer (gebucht) erhält Buchungsstatus 'kostenpflichtig storniert' durch Stornierung durch Führungskraft";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C14";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseCancelledWithCostsMembers();
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