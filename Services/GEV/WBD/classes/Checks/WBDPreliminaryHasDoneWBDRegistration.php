<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasDoneWBDRegistration extends WBDPreliminary {
	static $message = "User did not finish the WBD Registration Process.";

	public function message() {
		return self::$message;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->hasDoneWBDRegistration();
	}
}

