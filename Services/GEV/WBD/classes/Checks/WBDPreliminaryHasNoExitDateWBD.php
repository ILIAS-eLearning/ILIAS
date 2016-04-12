<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryHasNoExitDateWBD extends WBDPreliminary {
	static $message = "gev_wbd_check_user_is_released";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->hasExitDateWBD();
	}
}