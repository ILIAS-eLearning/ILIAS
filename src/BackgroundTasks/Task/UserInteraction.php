<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Value;

/**
 * Interface UserInteraction
 *
 * @package ILIAS\BackgroundTasks\Task
 *
 *          A Task in the Bucket, which will need some User-Interaction before running the task.
 */
interface UserInteraction extends Task
{

    /**
     * @param Value[] $input The input value of this task.
     *
     * @return Option[] Options are buttons the user can press on this interaction.
     */
    public function getOptions(array $input);


    /**
     * @param \ILIAS\BackgroundTasks\Value[] $input                The input value of this task.
     * @param Option                         $user_selected_option The Option the user chose.
     * @param Bucket                         $bucket               Notify the bucket about your
     *                                                             progress!
     *
     * @return Value
     */
    public function interaction(array $input, Option $user_selected_option, Bucket $bucket);
}
