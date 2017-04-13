<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Observer
 *
 * @package ILIAS\BackgroundTasks
 *
 *                Observers show a background task in the user interface.
 */
interface Observer {

	/**
	 * @return int
	 */
	public function getUserId();


	/**
	 * @param int $user_id
	 */
	public function setUserId($user_id);


	/**
	 * Used by a job to notify his percentage.
	 *
	 * @param $task       Task
	 * @param $percentage int
	 */
	public function notifyPercentage(Task $task, $percentage);

	/**
	 * @return int
	 */
	public function getPercentage();

	/**
	 * store the observerdata to persistence layer
	 */
	public function store();

	/**
	 * @param Task $task
	 * @return mixed
	 */
	public function setCurrentTask($task);

	/**
	 * @return Task
	 */
	public function getCurrentTask();

	/**
	 * @param Task $task
	 * @return void
	 */
	public function setTask(Task $task);

	/**
	 *
	 * @return Task
	 */
	public function getTask();

	/**
	 * @param $state int From Observer\State
	 * @return void
	 */
	public function notifyState($state);
}
