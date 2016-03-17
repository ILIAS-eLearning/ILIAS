<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryEntryDatePassed extends WBDPreliminary {
	static $message = "gev_wbd_checks_entry_date_not_passed";

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

