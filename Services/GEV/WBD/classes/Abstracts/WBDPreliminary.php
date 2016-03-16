<?php

abstract class WBDPreliminary {
	protected $parameter;
	static $message = "A General Error occourse";

	public function __construct($paramter = null) {
		$this->paramter = $paramter;
	}

	public function message() {
		return self::$message;
	}

	final public function setCheckParameter($check_paramter) {
		$this->check_paramter = $check_paramter;
	}

	/**
	 * perfoms the needed check
	 *
	 * @param gevWBD 	$wbd
	 */
	abstract function performCheck(gevWBD $wbd);
}