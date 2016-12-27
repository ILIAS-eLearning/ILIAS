<?php

/**
 * Interface ilBTJob
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-rai
 */
interface ilBTJob extends ilBTTask {

	/**
	 * @param $input ilBTIO
	 * @return ilBTIO
	 */
	public function run($input);

	/**
	 * @return bool Returns true iff the job supports giving feedback about the percentage done.
	 */
	public function supportsPercentage();


	/**
	 * @return int Returns 0 if !supportsPercentage and the percentage otherwise.
	 */
	public function getPercentage();


	/** @return returns true iff the job's output ONLY depends on the input. Stateless task results may be cached! */
	public function isStateless();

}
