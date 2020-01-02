<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Bucket;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Persistence;
use ILIAS\BackgroundTasks\Task;

/**
 * Class PersistingObserver
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 *
 * When notifying something to the bucket this observer also persists the changes into the database.
 */
class PersistingObserver implements Observer
{
    protected $bucket;
    protected $persistence;


    public function __construct(Bucket $bucket, Persistence $persistence)
    {
        $this->bucket = $bucket;
        $this->persistence = $persistence;
    }


    /**
     * @param $state int
     *
     */
    public function notifyState($state)
    {
        $this->bucket->setState($state);
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
    }


    /**
     * @param Task $task
     * @param int  $percentage
     *
     */
    public function notifyPercentage(Task $task, $percentage)
    {
        $this->bucket->setPercentage($task, $percentage);
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
    }


    /**
     * @param Task $task
     */
    public function notifyCurrentTask(Task $task)
    {
        $this->bucket->setCurrentTask($task);
        $this->bucket->heartbeat();
        $this->persistence->updateBucket($this->bucket);
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
        $this->persistence->updateBucket($this->bucket);
    }
}
