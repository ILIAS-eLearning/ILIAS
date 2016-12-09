<?php

/**
 * Interface ilBTJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
interface ilBTJob {

	/**
	 * @return string Class-Name of the ilBTIO
	 */
	public function getInputType();


	/**
	 * @return string
	 */
	public function getOutputType();


	public function run();


	/**
	 * @param ilBTIO $input
	 */
	public function setInput(ilBTIO $input);


	/**
	 * @return ilBTIO
	 */
	public function getOutput();


	/**
	 * @return bool
	 */
	public function isRunning();


	/**
	 * @return bool
	 */
	public function supportsPercentage();


	/**
	 * @return float
	 */
	public function getPercentage();


	/**
	 * @return bool returns true iff the job's output ONLY depends on the input
	 */
	public function isStateless();
}
