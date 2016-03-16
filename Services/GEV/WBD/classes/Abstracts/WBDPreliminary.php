<?php

abstract class WBDPreliminary {
	static $message = "A General Error occourse";

	public function message() {
		return self::$message;
	}

	/**
	 * perfoms the needed check
	 *
	 * @param gevWBD 	$wbd
	 */
	abstract function performCheck(gevWBD $wbd);
}