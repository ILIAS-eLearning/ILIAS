<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasWBDRelevantRole extends WBDPreliminary {
	static $message = "User has no Role thats marks him to be WBD relevant.";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->hasWBDRelevantRole();
	}
}

