<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

/**
 * Interface UserInteraction
 *
 * @package ILIAS\BackgroundTasks\Task
 *
 *          A Task in the Bucket, which will need some User-Interaction before running the task. A
 *          User-Interaction is provided as
 */
interface UserInteraction extends Task {

	/**
	 * @param Value $input The input value of this task.
	 * @return UserInteractionOption[] Options are buttons the user can press on this interaction.
	 */
	public function getOptions(Value $input);


	/**
	 * @param \ILIAS\BackgroundTasks\Value $input The input value of this task.
	 * @param UserInteractionOption $user_selected_option The Option the user chose.
	 * @param Observer $observer Notify the observer about your progress!
	 * @return Value
	 */
	public function interaction(Value $input, UserInteractionOption $user_selected_option, Observer $observer);
}
