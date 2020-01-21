<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

/**
 * Interface TaskManager
 *
 * @package ILIAS\BackgroundTasks
 *
 * We add, remove or observe buckets with the background task manager.
 *
 */
interface TaskManager
{

    /**
     * Actually executes a task.
     *
     * @param Task     $task
     * @param Observer $observer
     *
     * @return mixed
     */
    public function executeTask(Task $task, Observer $observer);


    /**
     *
     * Depending on your background task settings, executes or puts the task into the queue.
     *
     * @param Bucket $bucket
     *
     * @return mixed
     * @internal param int $userId
     * @internal param Task $task
     *
     */
    public function run(Bucket $bucket);


    /**
     * Continue a task that is the state UserInteraction with a given option.
     *
     * @param Bucket $bucket
     * @param Option $option
     *
     * @return mixed
     */
    public function continueTask(Bucket $bucket, Option $option);


    /**
     * Quits and deletes a Bucket with all it's Jobs
     *
     * @param \ILIAS\BackgroundTasks\Bucket $bucket
     */
    public function quitBucket(Bucket $bucket);
}
