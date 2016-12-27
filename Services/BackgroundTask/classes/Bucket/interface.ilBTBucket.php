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
	 * @param ilBTTask $task Add a job to the chain.
	 * @return ilBTBucket
	 */
	public function addTask($task);

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
	 * @return ilBTTask
	 */
	public function getRunningTask();

	/**
	 * @return int
	 */
	public function countTasks();

	/**
	 * @return int Returns the position of the running job in the bucket. Will be between 1 and countJobs()
	 */
	public function getRunningTaskPosition();

	/**
	 * @return int Tries to estimate a percentage for the whole bucket.
	 */
	public function getOverallPercentage();

	/**
	 * @return void
	 */
	public function runBucket();

	/**
	 * @return void
	 */
	public function runOneTask();

	/**
	 * @param $exception_handler ilBTExceptionHandler
	 * @return ilBTBucket
	 */
	public function setExceptionHandler(ilBTExceptionHandler $exception_handler);

	/**
	 * @return ilBTBucket Persists the bucket into the database.
	 */
	public function putInQueue();

	/**
	 * @return string
	 */
	public function getTitle();

	/**
	 * @param $title string
	 * @return void
	 */
	public function setTitle($title);

	/**
	 * @param $user_ids int[]
	 * @return void
	 */
	public function putInQueueAndObserve($user_ids);
}