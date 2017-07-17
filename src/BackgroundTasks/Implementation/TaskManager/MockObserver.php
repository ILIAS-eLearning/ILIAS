<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

/**
 * Class MockObserver
 *
 * @package ILIAS\BackgroundTasks\Implementation\TaskManager
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class MockObserver implements Observer {

	/**
	 * @param $state int
	 *
	 */
	public function notifyState($state) {
		// Does nothing.
	}


	/**
	 * @param Task $task
	 * @param int  $percentage
	 *
	 */
	public function notifyPercentage(Task $task, $percentage) {
		// Does nothing.
	}


	/**
	 * @param Task $task
	 *
	 */
	public function notifyCurrentTask(Task $task) {
		// Does nothing.
	}
}
