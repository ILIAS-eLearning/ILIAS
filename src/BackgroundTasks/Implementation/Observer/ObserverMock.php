<?php

namespace ILIAS\BackgroundTasks\Implementation\Observer;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

class ObserverMock implements Observer {

	/**
	 * @return int
	 */
	public function getUserId() {
		// TODO: Implement getUserId() method.
	}

	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id) {
		// TODO: Implement setUserId() method.
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
	 * @param $task Task
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
	 * @param string $taskId
	 * @return mixed
	 */
	public function setCurrentTask($taskId) {
		// TODO: Implement setCurrentTask() method.
	}

	/**
	 * @param Task $task
	 * @return void
	 */
	public function setTask(Task $task) {
		// TODO: Implement setTask() method.
	}

	/**
	 * @return int
	 */
	public function getPercentage() {
		// TODO: Implement getPercentage() method.
	}

	/**
	 * @param $state int From Observer\State
	 * @return void
	 */
	public function notifyState($state) {
		// TODO: Implement notifyState() method.
	}
}
