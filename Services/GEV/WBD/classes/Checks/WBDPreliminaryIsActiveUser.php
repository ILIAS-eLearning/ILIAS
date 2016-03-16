<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryIsActiveUser extends WBDPreliminary {
	static $message = "User is not active.";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->isActive();
	}
}

