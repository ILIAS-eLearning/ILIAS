<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevMinParticipantsNotReached extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 31;
	
	public function getTitle() {
		return "Info Admin";
	}
	
	public function _getDescription() {
		// Mail is send after the 31th day before training is over.
		// Thus we need to subtract, since after the 31th day is on the
		// 30th day.
		return (self::DAYS_BEFORE_COURSE_START - 1)." Tage vor Trainingsbeginn wenn Mindesteilnahmerzahl nicht erreicht";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date !== null) {
			$date->increment(IL_CAL_DAY, -1 * self::DAYS_BEFORE_COURSE_START);
		}
		return $date;
	}
	
	public function shouldBeSend() {
		$utils = $this->getCourseUtils();
		if ($utils->getMinParticipants() <= count($utils->getParticipants())) {
			return false;
		}
		
		return parent::shouldBeSend();
	}

	
	public function getTemplateCategory() {
		return "R01";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseAdmins();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>