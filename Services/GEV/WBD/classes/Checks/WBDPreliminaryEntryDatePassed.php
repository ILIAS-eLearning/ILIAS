<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryEntryDatePassed extends WBDPreliminary {
	static $message = "User has not passed the entry date.";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->entryDatePassed();
	}
}

