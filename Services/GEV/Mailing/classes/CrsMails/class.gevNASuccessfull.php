<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevNASuccessfull extends gevCrsAutoMail {
	public function getTitle() {
		return "Info ADSS/ADSN";
	}
	
	public function _getDescription() {
		return "NA erhält Teilnahmestatus 'teilgenommen'";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "F04";
	}
	
	public function getRecipientUserIDs() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$recp = array();
		foreach ($this->getCourseSuccessfullParticipants() as $part) {
			if (gevUserUtils::getInstance($part)->isNA()) {
				$recp[] = $part;
			}
		}
		return $recp;
	}
	
	public function getCC($a_recipient) {
		return array();
	}
	
	public function getMessage($a_template_id, $a_recipient) {
		if (!$this->checkUserID($a_recipient)) {
			throw new Exception("NASuccessfull-Mail will only work for ILIAS-Users.");
		}
		
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		if (!gevUserUtils::getInstance($a_recipient)->isNA()) {
			return null;
		}
		
		$message = parent::getMessage($a_template_id, $a_recipient);
		
		require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
		$message["to"] = gevNAUtils::getNASuccessfullMailRecipient($a_recipient);
		
		return $message;
	}
}

?>