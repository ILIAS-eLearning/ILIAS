<?php

/**
 * Class ilAtomQueryTestHelperSettings
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilAtomQueryTestHelperSettings {

	/**
	 * @var int
	 */
	protected $throw_exceptions = 0;
	/**
	 * @var bool
	 */
	protected $simulate_deadlock = false;


	/**
	 * @return boolean
	 */
	public function isSimulateDeadlock() {
		return $this->simulate_deadlock;
	}


	/**
	 * @param boolean $simulate_deadlock
	 */
	public function setSimulateDeadlock($simulate_deadlock) {
		$this->simulate_deadlock = $simulate_deadlock;
	}


	/**
	 * @return int
	 */
	public function getThrowExceptions() {
		return $this->throw_exceptions;
	}


	/**
	 * @param int $throw_exceptions
	 */
	public function setThrowExceptions($throw_exceptions) {
		$this->throw_exceptions = $throw_exceptions;
	}
}
