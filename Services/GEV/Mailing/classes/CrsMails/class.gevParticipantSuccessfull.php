<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantSuccessfull extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Teilnahmestatus 'teilgenommen'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F01";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseSuccessfullParticipants();
	}
	
	public function getCC($a_recipient) {
		return array();
	}

	public function getMail($a_recipient) {
		require_once("Services/GEV/Utils/classes/class.gevExpressLoginUtils.php");
		$exprUserUtils = gevExpressLoginUtils::getInstance();

		if($exprUserUtils->isExpressUser($a_recipient)){
			return null;
		}

		return parent::getMail($a_recipient);
	}
}

?>