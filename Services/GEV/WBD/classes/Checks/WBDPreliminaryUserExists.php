<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryUserExists  extends WBDPreliminary {
	static $message = "gev_wbd_check_user_not_exist";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->userExists();
	}
}