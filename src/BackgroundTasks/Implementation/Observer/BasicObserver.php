<?php

namespace ILIAS\BackgroundTasks\Implementation\Observer;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

class BasicObserver implements Observer {

	/**
	 * @var int
	 */
	protected $userId;

	/**
	 * @var Task
	 */
	protected $rootTask;

	/**
	 * @var Task
	 */
	protected $currentTask;

	/**
	 * @var Task[]
	 */
	protected $tasks;

	/**
	 * @var int
	 */
	protected $state;

	/**
	 * @var int
	 */
	protected $totalNumberOfTasks;

	/**
	 * @var int[]
	 */
	protected $percentages = [];

	/**
	 * @return int
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * @param int $user_id
	 * @return $this BasicObserver
	 */
	public function setUserId($user_id) {
		$this->userId = $user_id;
		return $this;
	}

	/**
	 * Used by a job to notify his percentage.
	 *
	 * @param $task       Task
	 * @param $percentage int
	 */
	public function notifyPercentage(Task $task, $percentage) {
		$this->percentages[spl_object_hash($task)] = $percentage;
	}

	/**
	 * store the observerdata to persistence layer
	 */
	public function store() {
		// TODO: Implement store() method.
	}

	/**
	 * @param Task $task
	 * @return mixed
	 */
	public function setCurrentTask($task) {
		$this->currentTask = $task;
	}

	/**
	 * @param Task $task
	 * @return void
	 */
	public function setTask(Task $task) {
		$this->tasks = $task->unfoldTask();
		$this->totalNumberOfTasks = count($this->tasks);
		$this->currentTask = $task;
		$this->rootTask = $task;
		foreach ($this->tasks as $subTask)
			$this->percentages[spl_object_hash($subTask)] = 0;
	}

	/**
	 * @return int
	 */
	public function getPercentage() {
		return array_sum($this->percentages) / $this->totalNumberOfTasks;
	}

	/**
	 * @param int $state From ILIAS\BackgroundTasks\Implementation\Observer\State
	 */
	public function notifyState($state) {
		$this->state = $state;
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId(int $id) {
		$this->id = $id;
	}
}