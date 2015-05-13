<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevTrainingCancelledTrainerInfo extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Trainer Trainingsabsage";
	}
	
	public function _getDescription() {
		return "Trainer wird benachrichtigt, dass das Training abgesagt wurde.";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C17";
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