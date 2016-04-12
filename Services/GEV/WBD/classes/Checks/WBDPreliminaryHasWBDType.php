<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryHasWBDType extends WBDPreliminary {
	protected $wbd_type;
	static $message = "gev_wbd_check_user_has_wrong_service_type";

	public function __construct($wbd_type) {
		assert(is_string($wbd_type));

		$this->wbd_type = $wbd_type;
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->hasWBDType($this->wbd_type);
	}
}
