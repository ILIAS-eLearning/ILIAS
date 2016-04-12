<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryHasNoOpenWBDError extends WBDPreliminary {
	static $message = "gev_wbd_check_open_wbd_errors";
	protected $wbd_errors;

	public function __construct(array $wbd_errors) {
		$this->wbd_errors = $wbd_errors;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->hasOpenWBDErrors($wbd_errors);
	}
}

