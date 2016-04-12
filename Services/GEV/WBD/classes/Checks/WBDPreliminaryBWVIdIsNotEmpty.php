<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryBWVIdIsNotEmpty extends WBDPreliminary {
	static $message = "gev_wbd_checks_bwvid_empty";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->isWBDBWVIdEmpty();
	}
}