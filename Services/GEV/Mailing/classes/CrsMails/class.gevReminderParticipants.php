<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevReminderParticipants extends gevCrsAutoMail {
	const DAYS_BEFORE_COURSE_START = 3;
	
	public function getTitle() {
		return "Erinnerung Teilnehmer";
	}
	
	public function _getDescription() {
		return self::DAYS_BEFORE_COURSE_START." Tage vor Trainingsbeginn";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getStartDate();
		if ($date) {
			$date->increment(IL_CAL_DAY, -1 * self::DAYS_BEFORE_COURSE_START);
		}
		return $date;
	}
	
	public function getTemplateCategory() {
		return "R02";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseMembers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>