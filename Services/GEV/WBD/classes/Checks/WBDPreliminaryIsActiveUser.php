<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryIsActiveUser extends WBDPreliminary {
	static $message = "gev_wbd_check_user_is_inactive";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->isActive();
	}
}

