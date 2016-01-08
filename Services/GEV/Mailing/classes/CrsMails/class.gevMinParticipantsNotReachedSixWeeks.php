<?php

require_once("Services/GEV/Mailing/classes/CrsMails/class.gevMinParticipantsNotReached.php");

class gevMinParticipantsNotReachedSixWeeks extends gevMinParticipantsNotReached {
	const DAYS_BEFORE_COURSE_START = 43;
	
	public function getTitle() {
		return "Info Admin Six Week";
	}
}

?>