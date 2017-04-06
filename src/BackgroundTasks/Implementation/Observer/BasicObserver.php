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
	 * @return int
	 */
	public function getBucketId() {
		// TODO: Implement getBucketId() method.
	}

	/**
	 * @param int $bucket_id
	 */
	public function setBucketId($bucket_id) {
		// TODO: Implement setBucketId() method.
	}

	/**
	 * Used by a job to notify his percentage.
	 *
	 * @param $task       Task
	 * @param $percentage int
	 */
	public function notifyPercentage(Task $task, $percentage) {
		// TODO: Implement notifyPercentage() method.
	}

	/**
	 * store the observerdata to persistence layer
	 */
	public function store() {
		// TODO: Implement store() method.
	}

	/**
	 * @param int $taskId
	 * @return mixed
	 */
	public function setCurrentTask(int $taskId) {
		// TODO: Implement setCurrentTask() method.
	}
}