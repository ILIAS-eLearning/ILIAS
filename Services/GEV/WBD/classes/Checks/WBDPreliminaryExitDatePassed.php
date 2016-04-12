<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryExitDatePassed extends WBDPreliminary {
	static $message = "gev_wbd_checks_exit_date_not_passed";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->exitDatePassed();
	}
}
