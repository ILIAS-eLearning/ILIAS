<?php

namespace ILIAS\BackgroundTasks\Implementation\Bucket;

use ILIAS\BackgroundTasks\Exception;
use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

class BucketMock implements Bucket {

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
	public function setPercentage(Task $task, $percentage) {
		// TODO: Implement notifyPercentage() method.
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
	public function setState($state) {
		// TODO: Implement notifyState() method.
	}


	/**
	 * @return Task
	 */
	public function getCurrentTask() {
		// TODO: Implement getCurrentTask() method.
	}


	/**
	 *
	 * @return Task
	 */
	public function getTask() {
		// TODO: Implement getTask() method.
	}


	/**
	 * @return int
	 */
	public function getState() {
		// TODO: Implement getState() method.
	}


	/**
	 * @return boolean      Returns true if everything's alright. Throws an exception otherwise.
	 * @throws Exception
	 */
	public function checkIntegrity() {
		// TODO: Implement checkIntegrity() method.
	}


	/**
	 * Let the user interact with the bucket task queue.
	 *
	 * @param Option $option
	 *
	 * @return void
	 */
	public function userInteraction(Option $option) {
		// TODO: Implement userInteraction() method.
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		// TODO: Implement getDescription() method.
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		// TODO: Implement getTitle() method.
	}
}
