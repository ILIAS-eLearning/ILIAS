<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevMinParticipantsNotReached extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 30;
	
	public function getTitle() {
		return "Info Admin";
	}
	
	public function _getDescription() {
		return self::DAYS_BEFORE_COURSE_START." Tage vor Trainingsbeginn wenn Mindesteilnahmerzahl nicht erreicht";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date !== null) {
			$date->increment(-1 * self::DAYS_BEFORE_COURSE_START, IL_CAL_DATE);
		}
		return $date;
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