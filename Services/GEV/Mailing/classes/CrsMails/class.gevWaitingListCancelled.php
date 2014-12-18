<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevWaitingListCancelled extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Warteliste wird automatisch abgesagt";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C10";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseCancelledWithoutCostsMembers();
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