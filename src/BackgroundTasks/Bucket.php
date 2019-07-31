<?php

namespace ILIAS\BackgroundTasks;

use ILIAS\BackgroundTasks\Exceptions\Exception;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;

/**
 * Interface Observer
 *
 * @package ILIAS\BackgroundTasks
 *
 * Contains several chained tasks and infos about them.
 */
interface Bucket extends BucketMeta
{

    /**
     * @return int
     */
    public function getUserId();


    /**
     * @param int $user_id
     */
    public function setUserId($user_id);


    /**
     * Used by a job to notify his percentage.
     *
     * @param $task       Task
     * @param $percentage int
     */
    public function setPercentage(Task $task, $percentage);


    /**
     * @return integer
     */
    public function getOverallPercentage();


    /**
     * @param $percentage int
     *
     */
    public function setOverallPercentage($percentage);


    /**
     * @param Task $task
     *
     * @return mixed
     */
    public function setCurrentTask($task);


    /**
     * @return Task
     */
    public function getCurrentTask();


    /**
     * @param Task $task
     *
     * @return void
     */
    public function setTask(Task $task);


    /**
     *
     * @return Task
     */
    public function getTask();


    /**
     * @param $state int From Observer\State
     *
     * @return void
     */
    public function setState($state);


    /**
     * @return int
     */
    public function getState();


    /**
     * @return boolean      Returns true if everything's alright. Throws an exception otherwise.
     * @throws Exception
     */
    public function checkIntegrity();


    /**
     * Let the user interact with the bucket task queue.
     *
     * @param Option $option
     *
     * @return void
     */
    public function userInteraction(Option $option);


    /**
     * @return string
     */
    public function getDescription();


    /**
     * @return string
     */
    public function getTitle();


    /**
     * There was something going on in the bucket, it's still working.
     *
     * @return void
     */
    public function heartbeat();


    /**
     * @param $timestamp int
     *
     * @return void
     */
    public function setLastHeartbeat($timestamp);


    /**
     * When was the last time that something happened on this bucket?
     *
     * @return int Timestamp.
     */
    public function getLastHeartbeat();
}
