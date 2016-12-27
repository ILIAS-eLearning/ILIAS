<?php

/**
 * Class ilBTTaskBase
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class ilBTTaskBase implements ilBTTask {
	/** @var ilBTIO **/
	protected $input;

	/** @var ilBTIO **/
	protected $output;

	/**
	 * @return ilBTIO
	 */
	public function getOutput() {
		return $this->output;
	}

	/**
	 * @param $input ilBTIO
	 */
	public function setInput(ilBTIO $input) {
		$this->input = $input;
	}

}