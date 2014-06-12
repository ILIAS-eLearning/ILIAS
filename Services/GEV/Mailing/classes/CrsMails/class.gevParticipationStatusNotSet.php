<?php

require_once("Services/GEV/Mailing/classes/class.gevCrsAutoMail.php");

class gevParticipationStatusNotSet extends gevCrsAutoMail {
	const DAYS_AFTER_COURSE_END = 2;
	
	public function getTitle() {
		return "Erinnerung Trainer";
	}
	
	public function _getDescription() {
		return "Trainer hat Teilnahmestatus noch nicht gesetzt";
	}
	
	public function getScheduledFor() {
		$date = $this->getCourseUtils()->getEndDate();
		if ($date) {
			$date->increment(self::DAYS_AFTER_COURSE_END, IL_CAL_DAY);
		}
		return $date;
	}
	
	public function getTemplateCategory() {
		return "R6";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseFullTrainers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>