<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasNotWBDType extends WBDPreliminary {
	protected $wbd_type;
	static $message = "User has the WBD Type %s";

	public function __construct($wbd_type) {
		assert(is_string($wbd_type));

		$this->wbd_type = $wbd_type;
	}

	public function message() {
		return sprintf(self::$message, $this->wbd_type);
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return !$wbd->hasWBDType($this->wbd_type);
	}
}
