<?php

namespace ILIAS\BackgroundTasks\Implementation;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Exceptions\NoObserverForUserInteractionException;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Worker;

class BasicTaskManager implements TaskManager {

	public function __construct() {

	}

	/**
	 * @param Task $task
	 * @param Observer $observer
	 * @return Value
	 * @throws Exception
	 * @throws \UserInteractionRequiredException
	 */
	public function executeTask(Task $task, Observer $observer) {
		/** @var Value[] $values */
		$values = $task->getInput();
		$final_values = [];
		foreach ($values as $value) {
			if(is_a($value, ThunkValue::class))
				$value = $this->executeTask($value->getParentTask(), $observer);
				// TODO: Replace Thunk with actual value.
			$final_values[] = $value;
		}

		if(is_a($task, Task\Job::class)) {
			/** @var Task\Job $job */
			$job = $task;
			$observer->setCurrentTask($job->getId());
			return $job->run($final_values, $observer);
		}

		if(is_a($task, Task\UserInteraction::class)) {
			/** @var Task\UserInteraction $userInteraction */
			$userInteraction = $task;
			$observer->setCurrentTask($userInteraction->getId());
			throw new \UserInteractionRequiredException("User interaction required.");
		}

		throw new Exception("You need to execute a Job or a UserInteraction.");
	}

	/**
	 * This will add an Observer of the Task and start running the task.
	 *
	 * @param int $userId
	 * @param Task $task
	 * @throws Exception
	 */
	public function observeAndExecuteTask(int $userId, Task $task) {
		throw new Exception("Not implemented yet.");
	}

	/**
	 * @param $bucket Bucket
	 * @param $user_ids int[]
	 * @return Bucket
	 *
	 * @throws NoObserverForUserInteractionException Is thrown when the user_id(s) cannot be resolved to a user. Thus we would have a user interaction without a user.
	 */
	public function putInQueueAndObserve(Bucket $bucket, $user_ids) {
		// TODO: Implement putInQueueAndObserve() method.
	}

	/**
	 * @param $bucket
	 * @return mixed
	 */
	public function removeBucket($bucket) {
		// TODO: Implement removeBucket() method.
	}

	/**
	 * @param $bucket
	 * @param $user_id
	 * @return mixed
	 */
	public function addObserver($bucket, $user_id) {
		// TODO: Implement addObserver() method.
	}

	/**
	 * @param $bucket
	 * @param $user_id
	 * @return mixed
	 */
	public function removeObserver($bucket, $user_id) {
		// TODO: Implement removeObserver() method.
	}

	/**
	 * @return Worker
	 */
	public function getWorker() {
		// TODO: Implement getWorker() method.
	}
}