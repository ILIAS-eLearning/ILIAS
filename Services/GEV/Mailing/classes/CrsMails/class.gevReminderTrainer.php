<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevReminderTrainer extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 4;
	
	public function getTitle() {
		return "Erinnerung Trainer";
	}
	
	public function _getDescription() {
		// Mail is send after the fourth day before training is over.
		// Thus we need to subtract, since after the fourth day is on the
		// third day.
		return (self::DAYS_BEFORE_COURSE_START - 1)." Tage vor Trainingsbeginn";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date) {
			$date->increment(IL_CAL_DAY, -1 * self::DAYS_BEFORE_COURSE_START);
		}
		return $date;
	}
	
	public function getTemplateCategory() {
		//return "R03";
		return "B07";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseTrainers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>