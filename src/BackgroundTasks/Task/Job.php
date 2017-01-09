<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\IO;
use ILIAS\BackgroundTasks\Task;

/**
 * Interface Job
 *
 * @package ILIAS\BackgroundTasks\Task
 *
 *          A Task, which can be run without any interaction with the user such as zipping files or just collecting some data
 */
interface Job extends Task {

	/**
	 * @param \ILIAS\BackgroundTasks\IO $input
	 * @return \ILIAS\BackgroundTasks\IO
	 */
	public function run(IO $input);


	/**
	 * @return bool Returns true iff the job supports giving feedback about the percentage done.
	 */
	public function supportsPercentage();


	/**
	 * @return int Returns 0 if !supportsPercentage and the percentage otherwise.
	 */
	public function getPercentage();


	/**
	 * @return bool returns true iff the job's output ONLY depends on the input. Stateless task results may be cached!
	 */
	public function isStateless();
}
