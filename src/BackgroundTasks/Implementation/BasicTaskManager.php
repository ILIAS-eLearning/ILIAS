<?php

namespace ILIAS\BackgroundTasks\Implementation;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Exceptions\NoObserverForUserInteractionException;
use ILIAS\BackgroundTasks\Implementation\Observer\BasicObserver;
use ILIAS\BackgroundTasks\Implementation\Observer\State;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Worker;

/**
 * Class BasicTaskManager
 * @package ILIAS\BackgroundTasks\Implementation
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 * Basic Task manager. Will execute tasks immediately.
 *
 */
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
		$observer->notifyState(State::RUNNING);
		/** @var Value[] $values */
		$values = $task->getInput();
		$final_values = [];
		$replace_thunk_values = false;
		foreach ($values as $value) {
			if(is_a($value, ThunkValue::class)) {
				$value = $this->executeTask($value->getParentTask(), $observer);
				$replace_thunk_values = true;
			}
			$final_values[] = $value;
		}

		if ($replace_thunk_values) {
			$task->setInput($final_values);
		}

		if(is_a($task, Task\Job::class)) {
			/** @var Task\Job $job */
			$job = $task;
			$observer->setCurrentTask($job);
			$value = $job->run($final_values, $observer);
			$observer->notifyPercentage($job, 100);
			return $value;
		}

		if(is_a($task, Task\UserInteraction::class)) {
			/** @var Task\UserInteraction $userInteraction */
			$userInteraction = $task;
			$observer->setCurrentTask($userInteraction);
			$observer->notifyState(State::USER_INTERACTION);
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
	 * @throws \Exception
	 */
	public function observeAndExecuteTask(int $userId, Task $task) {
		$observer = new BasicObserver();
		$observer->setUserId($userId);
		$observer->setTask($task);

		try {
			$this->executeTask($task, $observer);
			$observer->notifyState(State::FINISHED);
		} catch (\UserInteractionRequiredException $e) {
			// We're okay!
			// TODO: Write Task and Observer into the Database.
		} catch (\Exception $e) {
			// As we are Synchronous execution we rethrow the error for the caller to handle.
			throw $e;
		}
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