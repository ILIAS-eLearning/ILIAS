<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Bucket
 *
 * @package ILIAS\BackgroundTasks
 *
 *          A Bucket is used to pack several Tasks which have to be done one after the other and is put in the background to avoid hight impact on
 *          the ILIAS system.
 *
 *          Whenever packing a bucket, you start with an Input (IO-interface) wich will be passed to the first task. your task will provide an output
 *          (IO-interface again) wich will be passed to the next Task, ...
 *
 *          Bucket (IO -> Task -> IO -> Task)
 *
 *          Please notice that Buckets are mutable!
 */
interface Bucket extends Task {

	/**
	 * @param \ILIAS\BackgroundTasks\IO $input
	 * @return Bucket
	 */
	public function setInput(IO $input);


	/**
	 * @param \ILIAS\BackgroundTasks\Task $task
	 * @return Bucket
	 */
	public function addTask(Task $task);


	/**
	 * Returns true iff the input and outputs of the chain match
	 *
	 * @return Exception[]
	 **/
	public function checkChain();


	/**
	 * @return bool
	 */
	public function isRunning();


	/**
	 * @return Task
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
	 * @param \ILIAS\BackgroundTasks\ExceptionHandler $exception_handler
	 * @return Bucket
	 */
	public function setExceptionHandler(ExceptionHandler $exception_handler);


	/**
	 * @return Bucket Persists the bucket into the database.
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
	 * @param \ILIAS\BackgroundTasks\Observer $observer when no observer is set, Common observer is used
	 * @return $this
	 */
	public function addObserver(Observer $observer);

	/**
	 * @param Observer $observer
	 * @return void
	 */
	public function removeObserver(Observer $observer);

	/**
	 * @return Observer[]
	 */
	public function getObserver();
}
