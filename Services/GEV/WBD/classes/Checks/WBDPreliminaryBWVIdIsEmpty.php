<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryBWVIdIsEmpty extends WBDPreliminary {
	static $message = "gev_wbd_checks_bwvid_not_empty";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->isWBDBWVIdEmpty();
	}
}