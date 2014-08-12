<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSelfCancelWaitingToCancelledWithoutCosts extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer (auf Warteliste) erhält Buchungsstatus 'kostenfrei storniert' durch Selbststornierung";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C02";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseCancelledWithoutCostsMembers();
	}
	
	public function getCC($a_recipient) {
		if (in_array( $this->getCourseUtils()->getType()
					, array("Präsenztraining", "Spezialistenschulung Präsenztraining")
					)
			) {
			require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
			return gevUserUtils::getInstance($a_recipient)->getDirectSuperiors();
		}
	}
}

?>