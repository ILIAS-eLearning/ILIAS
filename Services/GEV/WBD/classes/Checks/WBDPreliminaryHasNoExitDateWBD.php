<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasNoExitDateWBD extends WBDPreliminary {
	static $message = "User is released for WBD.";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->hasExitDateWBD();
	}
}