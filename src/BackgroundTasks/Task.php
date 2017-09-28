<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\Type;

/**
 * Interface Task
 *
 * @package ILIAS\BackgroundTasks
 *
 *          A Task is the basic interface of an "thing" which can be put into a Bucket and will be
 *          run or triggered by the BackgroundTask-Worker. Currently there are two types of Tasks:
 *          - Job: A Task, which can be run without any interaction with the user such as zipping
 *          files or just collecting some data
 *          - UserInteraction: A Task in the Bucket, which will need some User-Interaction before
 *          running the task. A User-Interaction is provided as Button in the UserInterface such as
 *          [ Cancel ] or [ Download ]
 */
interface Task {

	/**
	 * @return string
	 */
	public function getType();


	/**
	 * @return Type[] A list of types that are taken as input.
	 */
	public function getInputTypes();


	/**
	 * @return Type A single type.
	 */
	public function getOutputType();


	/**
	 * @return Value
	 */
	public function getOutput();


	/**
	 * @param $values (Value|Task)[]
	 *
	 * @return void
	 */
	public function setInput(array $values);


	/**
	 * @return Value[]
	 */
	public function getInput();


	/**
	 * @return Task[] A list of tasks that is chained with this task. The first element will be
	 *                this tasks, the following his dependencies.
	 */
	public function unfoldTask();


	/**
	 * @return int the amount of seconds this task usually taskes. If your task-duration scales
	 *             with the the amount of data, try to set a possible high value of try to
	 *             calculate it. If a task duration exceeds this value, it will be displayed as
	 *             "possibly failed" to the user
	 */
	public function getExpectedTimeOfTaksInSeconds();


	/**
	 * @return Option An Option to dismiss the current task and do some cleanup if possible. This
	 *                Option is diplayed if the Job is possibly failed or if a Bucket is completed.
	 *                You do not have to provide an additional Option to dismiss in your
	 *                UserInteraction, the dismiss-Option is added to the list of Options (last
	 *                position)
	 */
	public function getDismissOption();
}
