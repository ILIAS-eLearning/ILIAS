<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryHasWBDType extends WBDPreliminary {
	protected $parameter;
	static $message = "User has not the WBD Type %s";

	public function message() {
		return sprintf(self::$message, $this->parameter);
	}

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->hasWBDType($this->parameter);
	}
}
