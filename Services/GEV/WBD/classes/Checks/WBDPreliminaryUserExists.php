<?php
require_once ("Services/GEV/WBD/classes/Interfaces/WBDPreliminary.php");

class WBDPreliminaryUserExists  extends WBDPreliminary {
	protected $parameter;
	static $message = "User does not exist";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->userExists();
	}
}