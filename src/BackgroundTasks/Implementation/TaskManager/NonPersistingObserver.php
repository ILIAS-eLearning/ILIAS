<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

/**
 * Class NonPersistingObserver
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 * The NonPersistingObserver just updates the bucket, without persisting it into the database.
 */
class NonPersistingObserver implements Observer
{

    protected $bucket;


    public function __construct(Bucket $bucket)
    {
        $this->bucket = $bucket;
    }


    /**
     * @param $state int
     *
     */
    public function notifyState($state)
    {
        $this->bucket->heartbeat();
        $this->bucket->setState($state);
    }


    /**
     * @param Task $task
     * @param int  $percentage
     *
     */
    public function notifyPercentage(Task $task, $percentage)
    {
        $this->bucket->heartbeat();
        $this->bucket->setPercentage($task, $percentage);
    }


    /**
     * @param Task $task
     */
    public function notifyCurrentTask(Task $task)
    {
        $this->bucket->heartbeat();
        $this->bucket->setCurrentTask($task);
    }


    /**
     * I'm still alive! If your calculation takes a really long time don't forget to use the heartbeat. Otherwise
     * the bucket might be killed while still running. All notify tasks of the observer also trigger a heartbeat.
     *
     * @return void
     */
    public function heartbeat()
    {
        $this->bucket->heartbeat();
    }
}