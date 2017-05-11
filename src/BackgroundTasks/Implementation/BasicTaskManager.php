<?php
declare(strict_types=1);

namespace ILIAS\BackgroundTasks\Implementation;

use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Implementation\Observer\State;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionRequiredException;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\TaskManager;
use ILIAS\BackgroundTasks\Value;

/**
 * Class BasicTaskManager
 * @package ILIAS\BackgroundTasks\Implementation
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 * Basic Task manager. Will execute tasks immediately.
 *
 * Some important infos:
 *         - The observer and its tasks are not saved into the db upon execution
 *         - The percentage and current task are not updated during execution.
 *         - The observer and its tasks inkl. percentage and current task are only saved into the DB when a user interaction occurs.
 *
 */
class BasicTaskManager implements TaskManager {

	/**
	 * @var Persistence
	 */
	protected $persistence;

	public function __construct(Persistence $persistence) {
		$this->persistence = $persistence;
	}


	/**
	 * @param Task $task
	 * @param Observer $observer
	 * @return Value
	 * @throws Exception
	 */
	function executeTask(Task $task, Observer $observer) {
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
			throw new UserInteractionRequiredException("User interaction required.");
		}

		throw new Exception("You need to execute a Job or a UserInteraction.");
	}


	/**
	 * This will add an Observer of the Task and start running the task.
	 *
	 * @param Observer $observer
	 *
	 * @return mixed|void
	 * @throws \Exception
	 *
	 */
	public function run(Observer $observer) {
		$task = $observer->getTask();

		try {
			$this->executeTask($task, $observer);
			$observer->notifyState(State::FINISHED);
		} catch (UserInteractionRequiredException $e) {
			// We're okay!
			$this->persistence->saveObserverAndItsTasks($observer);
		} catch (\Exception $e) {
			// As we are Synchronous execution we rethrow the error for the caller to handle.
			throw $e;
		}
	}


	/**
	 * Continue a task with a given option.
	 *
	 * @param Observer $observer
	 * @param Option   $option
	 *
	 * @return mixed
	 */
	public function continueTask(Observer $observer, Option $option) {
		// We do the user interaction
		$observer->userInteraction($option);
		if($observer->getState() != State::FINISHED)
			// The job is not done after the user interaction, so we continue to run it.
			$this->run($observer);
		else {
			//TODO cleanup.
			echo "TODO.";
		}
	}
}