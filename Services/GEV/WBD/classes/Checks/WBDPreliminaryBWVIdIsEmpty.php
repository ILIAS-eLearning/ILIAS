<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryBWVIdIsEmpty extends WBDPreliminary {
	static $message = "gev_wbd_checks_bwvid_not_empty";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->isWBDBWVIdEmpty();
	}
}