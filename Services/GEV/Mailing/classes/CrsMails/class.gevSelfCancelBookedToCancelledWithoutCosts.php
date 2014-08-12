<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSelfCancelBookedToCancelledWithoutCosts extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer (gebucht) erhält Buchungsstatus 'kostenfrei storniert' durch Selbststornierung";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "C01";
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