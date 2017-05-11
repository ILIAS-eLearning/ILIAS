<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Exceptions\NoObserverForUserInteractionException;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

/**
 * Interface TaskManager
 *
 * @package ILIAS\BackgroundTasks
 *
 * We add, remove or observe buckets with the background task manager.
 *
 */
interface TaskManager {

	/**
	 * Actually executes a task.
	 *
	 * @param Task $task
	 * @param Observer $observer
	 * @return mixed
	 */
	public function executeTask(Task $task, Observer $observer);


	/**
	 *
	 * Depending on your background task settings, executes or puts the task into the queue.
	 *
	 * @param Observer $observer
	 *
	 * @return mixed
	 * @internal param int $userId
	 * @internal param Task $task
	 *
	 */
	public function run(Observer $observer);


	/**
	 * Continue a task with a given option.
	 *
	 * @param Observer $observer
	 * @param Option   $option
	 *
	 * @return mixed
	 */
	public function continueTask(Observer $observer, Option $option);
}