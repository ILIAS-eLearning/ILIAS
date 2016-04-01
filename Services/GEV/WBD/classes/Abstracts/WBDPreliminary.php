<?php

abstract class WBDPreliminary {
	static $message = "A General Error occurred";

	public function message() {
		return static::$message;
	}

	/**
	 * perfoms the needed check
	 *
	 * @param gevWBD 	$wbd
	 */
	abstract function performCheck(gevWBD $wbd);
}