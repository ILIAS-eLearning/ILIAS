<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryEntryDatePassed extends WBDPreliminary {
	static $message = "gev_wbd_checks_entry_date_not_passed";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->entryDatePassed();
	}
}

