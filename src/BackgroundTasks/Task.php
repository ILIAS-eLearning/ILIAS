<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Task
 *
 * @package ILIAS\BackgroundTasks
 *
 *          A Task is the basic interface of an "thing" which can be put into a Bucket and will be run or triggered by the BackgroundTask-Worker.
 *          Currently there are two types of Tasks:
 *          - Job: A Task, which can be run without any interaction with the user such as zipping files or just collecting some data
 *          - UserInteraction: A Task in the Bucket, which will need some User-Interaction before running the task. A User-Interaction is provided as
 *          Button in the UserInterface such as [ Cancel ] or [ Download ]
 */
interface Task {

	/**
	 * @return string
	 */
	public function getId();


	/**
	 * @return string Class-Name of the IO
	 */
	public function getInputType();


	/**
	 * @return string
	 */
	public function getOutputType();


	/**
	 * @return bool Returns true iff the job supports giving feedback about the percentage done.
	 */
	public function supportsPercentage();


	/**
	 * @return int Returns 0 if !supportsPercentage and the percentage otherwise.
	 */
	public function getPercentage();
}
