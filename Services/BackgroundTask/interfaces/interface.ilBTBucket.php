<?php

/**
 * Interface ilBTBucket
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 * The class represents a list of BTJobs that are chained.
 */
interface ilBTBucket {

	/**
	 * @param ilBTIO $input The input of the first job.
	 * @return ilBTBucket
	 */
	public function setInput(ilBTIO $input);


	/**
	 * @param ilBTJob $job Add a job to the chain.
	 * @return ilBTBucket
	 */
	public function addJob($job);


	/**
	 * Returns true iff the input and outputs of the chain match
	 *
	 * @return ilChainError[]
	 **/
	public function checkChain();


	/**
	 * @return bool
	 */
	public function isRunning();


	/**
	 * @return ilBTJob
	 */
	public function getRunningJob();


	/**
	 * @return int
	 */
	public function countJobs();


	/**
	 * @return int
	 */
	public function getRunningJobPosition();


	/**
	 * @return float
	 */
	public function getOverallPercentage();


	public function run();
}