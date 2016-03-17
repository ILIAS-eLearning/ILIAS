<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasNoExitDateWBD extends WBDPreliminary {
	static $message = "gev_wbd_check_user_is_released";

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