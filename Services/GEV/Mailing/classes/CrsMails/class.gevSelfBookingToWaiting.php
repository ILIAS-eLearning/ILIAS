<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevSelfBookingToWaiting extends gevCrsAutoMail {
	public function getTitle() {
		return "Info Teilnehmer";
	}
	
	public function _getDescription() {
		return "Teilnehmer erhält Buchungsstatus 'auf Warteliste' durch Selbstbuchung";
	}
	
	public function getScheduledFor() {
		return null;
	}
	
	public function getTemplateCategory() {
		return "B02";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseParticipants();
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