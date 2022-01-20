<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Interface Job
 * @package ILIAS\BackgroundTasks\Task
 *          A Task, which can be run without any interaction with the user, such as zipping files
 *          or just collecting some data
 */
interface Job extends Task
{
    
    /**
     * @param \ILIAS\BackgroundTasks\Value[] $input    This will be a list of Values hinted by
     *                                                 getInputTypes.
     * @param Observer                       $observer Notify the bucket about your progress!
     * @return Value                            The returned Value must be of the type hinted by
     *                                                 getOutputType.
     */
    public function run(array $input, Observer $observer) : Value;
    
    /**
     * @return bool returns true iff the job's output ONLY depends on the input. Stateless task
     *              results may be cached!
     */
    public function isStateless() : bool;
    
    /**
     * @return array returns the input array
     */
    public function getInput() : array;
    
    /**
     * @return int the amount of seconds this task usually taskes. If your task-duration scales
     *             with the the amount of data, try to set a possible high value of try to
     *             calculate it. If a task duration exceeds this value, it will be displayed as
     *             "possibly failed" to the user
     */
    public function getExpectedTimeOfTaskInSeconds() : int;
}
