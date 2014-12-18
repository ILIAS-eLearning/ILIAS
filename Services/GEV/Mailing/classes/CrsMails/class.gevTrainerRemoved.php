<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevTrainerRemoved extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Trainer";
	}
	
	public function _getDescription() {
		return "Trainer wird vom Training entfernt";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C07";
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