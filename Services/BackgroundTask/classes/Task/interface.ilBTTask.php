<?php

/**
 * Interface ilBTJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBTTask {

	/**
	 * @return string Class-Name of the ilBTIO
	 */
	public function getInputType();


	/**
	 * @return string
	 */
	public function getOutputType();


	/**
	 * @param $input
	 */
	public function setInput(ilBTIO $input);


	/**
	 * @return ilBTIO
	 */
	public function getOutput();

	/**
	 * @return bool
	 */
	public function isUserInteraction();

	/**
	 * @return bool
	 */
	public function isJob();

}
