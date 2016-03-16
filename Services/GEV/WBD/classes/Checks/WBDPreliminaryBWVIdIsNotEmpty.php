<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryBWVIdIsNotEmpty extends WBDPreliminary {
	static $message = "User has no BWV ID.";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->isWBDBWVIdEmpty();
	}
}