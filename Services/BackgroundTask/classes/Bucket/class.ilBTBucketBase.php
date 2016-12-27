<?php

class ilBTBucketBase implements ilBTBucket {

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var ilBTIO
	 */
	protected $input;

	/**
	 * @var ilBTTask[]
	 */
	protected $tasks;

	/**
	 * @var ilBTExceptionHandler
	 */
	protected $exceptionHandler;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var bool
	 */
	protected $running;

	public function __construct() {
		$this->exceptionHandler = new ilBTDefaultExceptionHandler();
	}

	/**
	 * @param ilBTIO $input The input of the first job.
	 * @return ilBTBucket
	 */
	public function setInput(ilBTIO $input) {
		$this->input = $input;
		return $this;
	}

	/**
	 * @param ilBTTask $task Add a job to the chain.
	 * @return ilBTBucket
	 */
	public function addTask($task) {
		$this->tasks[] = $task;
		return $this;
	}

	/**
	 * Returns true iff the input and outputs of the chain match
	 *
	 * @return ilBTTypeError[]
	 **/
	public function checkChain() {
		// TODO: Implement checkChain() method.
	}

	/**
	 * @return bool
	 */
	public function isRunning() {
		// TODO: Implement isRunning() method.
	}

	/**
	 * @return int
	 */
	public function countTasks() {
		return count($this->tasks);
	}

	/**
	 * @return int Returns the position of the running job in the bucket. Will be between 1 and countJobs()
	 */
	public function getRunningTaskPosition() {
		// TODO: Implement getRunningJobPosition() method.
	}

	/**
	 * @return int Tries to estimate a percentage for the whole bucket.
	 */
	public function getOverallPercentage() {
		if($this->getRunningJob()->supportsPercentage()) {
			round(100 / $this->countTasks() * ($this->getRunningTaskPosition() - 1));
		} else {
			$oneJob = 100 / $this->countTasks();
			return  round(100 / $this->countTasks() * ($this->getRunningTaskPosition() - 1) + $oneJob * $this->getRunningJob()->getPercentage() / 100);
		}
	}

	/**
	 * @return void
	 */
	public function runBucket() {
		try {
			while($this->count)
			$this->running = true;
			$nextTask = array_pop($this->tasks);
			// TODO: Implement run() method.
		} catch(ilBTException $e) {
			$this->running = false;
			$this->exceptionHandler->handleException($e, $this, $this->getRunningJob());
		}
	}

	protected function doTask($task) {

	}

	/**
	 *
	 */
	public function runOneTask() {
		try {
			$this->running = true;
			$this->doTask();
			$this->running = false;
		} catch(ilBTException $e) {
			$this->running = false;
			$this->exceptionHandler->handleException($e, $this, $this->getRunningJob());
		}
	}

	/**
	 * @param $exception_handler ilBTExceptionHandler
	 * @return void
	 */
	public function setExceptionHandler(ilBTExceptionHandler $exception_handler) {
		$this->exceptionHandler = $exception_handler;
	}


	public function putInQueue() {
		// TODO: Write the bucket container, io containers and task containers to the DB.
		return $this;
	}

	/**
	 * @param $user_ids int[]
	 * @return void
	 */
	public function putInQueueAndObserve($user_ids) {
		$this->putInQueue();

		foreach ($user_ids as $user_id) {
			$bucketObserver = new ilBucketObserver();
			$bucketObserver->setBucketId($this->getId());
			$bucketObserver->setUserId($user_id);
			$bucketObserver->create();
		}

		return $this;
	}


	/**
	 * @return ilBTTask
	 */
	public function getRunningTask() {
		// TODO: Implement getRunningTask() method.
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $title
	 * @return $this|ilBTBucket
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}
}