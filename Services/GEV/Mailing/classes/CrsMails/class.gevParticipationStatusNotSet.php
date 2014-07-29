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
			$date->increment(IL_CAL_DAY, self::DAYS_AFTER_COURSE_END);
		}
		return $date;
	}
	
	public function shouldBeSend() {
		$utils = $this->getCourseUtils();
		if ($utils->allParticipationStatusSet()) {
			return false;
		}
		
		return parent::shouldBeSend();
	}
	
	public function getTemplateCategory() {
		return "R06";
	}
	
	public function getRecipientUserIDs() {
		return $this->getCourseTrainers();
	}
	
	public function getCC($a_recipient) {
		return array();
	}
}

?>