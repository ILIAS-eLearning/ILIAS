<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Task
 *
 * @package ILIAS\BackgroundTasks
 *
 *          A Task is the basic interface of an "thing" wich can be put into a Bucket and will be run or triggered by the BackgroundTask-Worker.
 *          Currently there are two types of Tasks:
 *          - Job: A Task, which can be run without any interaction with the user such as zipping files or just collecting some data
 *          - UserInteraction: A Task in the Bucket, which will need some User-Interaction before running the task. A User-Interaction is provided as
 *          Button in the UserInterface such as [ Cancel ] or [ Download ]
 */
interface Task {

	/**
	 * @return string Class-Name of the IO
	 */
	public function getInputType();


	/**
	 * @return string
	 */
	public function getOutputType();


	/**
	 * @return bool
	 */
	public function isUserInteraction();


	/**
	 * @return bool
	 */
	public function isJob();
}
