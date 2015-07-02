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
		require_once("./Services/GEV/Utils/classes/class.gevUserUtils.php");
		return gevUserUtils::getInstance($a_recipient)->getDirectSuperiors();
	}
	
	protected function getMessage($a_template_id, $a_recipient) {
		return $this->swapToWithCC(parent::getMessage($a_template_id, $a_recipient));
	}
	
	public function getMail($a_recipient) {
		if ($this->getAdditionalMailSettings()->getSuppressMails()) {
			return null;
		}
		
		return parent::getMail($a_recipient);
	}
}

?>