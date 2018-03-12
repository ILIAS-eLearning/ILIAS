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
	 * @param \ILIAS\BackgroundTasks\Value[] $input    This will be a list of Values hinted by
	 *                                                 getInputTypes.
	 * @param Observer                       $observer Notify the bucket about your progress!
	 *
	 * @return Value                            The returned Value must be of the type hinted by
	 *                                          getOutputType.
	 */
	public function run(Array $input, Observer $observer);


	/**
	 * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
	 *              results may be cached!
	 */
	public function isStateless();

	/**
	 * @return int the amount of seconds this task usually taskes. If your task-duration scales
	 *             with the the amount of data, try to set a possible high value of try to
	 *             calculate it. If a task duration exceeds this value, it will be displayed as
	 *             "possibly failed" to the user
	 */
	public function getExpectedTimeOfTaskInSeconds();
}
