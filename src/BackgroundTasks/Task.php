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
interface Task
{

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
     * @return Option   An Option to remove the current task and do some cleanup if possible. This
     *                  Option is displayed if the Bucket is completed. You do not have to provide
     *                  an additional Option to remove in your UserInteraction, the remove-Option
     *                  is added to the list of Options (last position)
     *
     * @see self::getAbortOption();
     */
    public function getRemoveOption();


    /**
     * @return Option   In case a Job is failed or did not respond for some time, an Abort-Option
     *                  is displayed. There is already a Standard-Abort-Option registered, you can
     *                  override with your own and do some cleanup if possible.
     */
    public function getAbortOption();
}
