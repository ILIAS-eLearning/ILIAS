<?php

require_once("Services/GEV/Mailing/classes/class.gevRegistrationMail.php");

class gevNAConfirmationMail extends gevNARegistrationMail {
	public function getTitle() {
		return "NA-Bestätigung";
	}
	
	public function _getDescription() {
		return "Der Betreuer eines NA erhält diese Mail nachdem sich der NA registriert hat.";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "A01";
	}
}

?>