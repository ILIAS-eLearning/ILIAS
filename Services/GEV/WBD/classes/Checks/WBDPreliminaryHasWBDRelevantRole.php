<?php
require_once ("Services/GEV/WBD/classes/Abstracts/WBDPreliminary.php");

class WBDPreliminaryHasWBDRelevantRole extends WBDPreliminary {
	static $message = "gev_wbd_check_no_wbdrelevant_role";

	/** 
	 * @inheritdoc 
	 */
	public function performCheck(gevWBD $wbd) {
		return $wbd->hasWBDRelevantRole();
	}
}

