<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasDoneWBDRegistration extends WBDPreliminary {
	static $message = "gev_wbd_check_wbd_register_not_finished";

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

