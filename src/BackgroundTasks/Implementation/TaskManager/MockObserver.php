<?php

namespace ILIAS\BackgroundTasks\Implementation\TaskManager;

use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Task;

/**
 * Class MockObserver
 *
 * @package ILIAS\BackgroundTasks\Implementation\TaskManager
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
class MockObserver implements Observer
{

    /**
     * @param $state int
     *
     */
    public function notifyState($state)
    {
        // Does nothing.
    }


    /**
     * @param Task $task
     * @param int  $percentage
     *
     */
    public function notifyPercentage(Task $task, $percentage)
    {
        // Does nothing.
    }


    /**
     * @param Task $task
     *
     */
    public function notifyCurrentTask(Task $task)
    {
        // Does nothing.
    }


    /**
     * I'm still alive! If your calculation takes a really long time don't forget to use the heartbeat. Otherwise
     * the bucket might be killed while still running. All notify tasks of the observer also trigger a heartbeat.
     *
     * @return void
     */
    public function heartbeat()
    {
        // Does nothing.
    }
}
