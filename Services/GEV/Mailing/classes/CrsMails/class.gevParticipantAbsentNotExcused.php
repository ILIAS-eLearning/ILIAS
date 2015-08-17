<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipantAbsentNotExcused extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Teilnahmestatus 'fehlt unentschuldigt'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F03";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseAbsentParticipants();
	}
	
	public function getCC($a_recipient) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		if (!$this->checkUserID($a_recipient)) {
			return array();
		}

		$superior_ids = gevUserUtils::getInstance($a_recipient)->getDirectSuperiors();

		return $superior_ids;
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