<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantLeftCorporation extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer verlässt Unternehmen";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C11";
	}
	
	public function getRecipientUserIDs() {
		return array();
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