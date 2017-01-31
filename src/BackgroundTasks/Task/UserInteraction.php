<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\IO;
use ILIAS\BackgroundTasks\Observer;
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
	 * @param IO $input The input value of this task.
	 * @return Option[] Options are buttons the user can press on this interaction.
	 */
	public function getOptions(IO $input);


	/**
	 * @param \ILIAS\BackgroundTasks\IO $input The input value of this task.
	 * @param Option $user_selected_option     The Option the user chose.
	 * @param Observer $observer               Notify the observer about your progress!
	 * @return IO
	 */
	public function interaction(IO $input, Option $user_selected_option, Observer $observer);
}
