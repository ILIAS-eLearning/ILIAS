<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Task;

/**
 * Interface Job
 *
 * @package ILIAS\BackgroundTasks\Task
 *
 *          A Task, which can be run without any interaction with the user, such as zipping files
 *          or just collecting some data
 */
interface Job extends Task {

	/**
	 * @param \ILIAS\BackgroundTasks\Value[] $input
	 * @param Observer $observer Notify the observer about your progress!
	 * @return Value
	 */
	public function run(Array $input, Observer $observer);


	/**
	 * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
	 *              results may be cached!
	 */
	public function isStateless();
}
