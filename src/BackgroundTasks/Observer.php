<?php

namespace ILIAS\BackgroundTasks;

interface Observer {

	/**
	 * @param $state int
	 *
	 */
	public function notifyState($state);


	/**
	 * @param Task $task
	 * @param int  $percentage
	 *
	 */
	public function notifyPercentage(Task $task, $percentage);


	/**
	 * @param Task $task
	 *
	 */
	public function notifyCurrentTask(Task $task);
}