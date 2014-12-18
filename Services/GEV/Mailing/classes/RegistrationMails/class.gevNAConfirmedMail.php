<?php

require_once("Services/GEV/Mailing/classes/class.gevRegistrationMail.php");

class gevNAConfirmedMail extends gevNARegistrationMail {
	public function getTitle() {
		return "Bestätigung NA-Account";
	}
	
	public function _getDescription() {
		return "Ein NA erhält diese Mail, wenn er vom Betreuer bestätigt wurde.";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "A02";
	}
}

?>