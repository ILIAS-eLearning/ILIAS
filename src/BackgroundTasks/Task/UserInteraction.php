<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\IO;
use ILIAS\BackgroundTasks\Task;

/**
 * Interface UserInteraction
 *
 * @package ILIAS\BackgroundTasks\Task
 *
 *          A Task in the Bucket, which will need some User-Interaction before running the task. A User-Interaction is provided as
 */
interface UserInteraction extends Task {

	/**
	 * @return array returns an array with value => lang_var. What options can the user select?
	 */
	public function getOptions();


	/**
	 * @param \ILIAS\BackgroundTasks\IO $input
	 * @param string $user_seleced_option
	 * @return \ILIAS\BackgroundTasks\IO
	 */
	public function interaction(IO $input, $user_seleced_option);
}
