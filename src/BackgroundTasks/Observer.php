<?php

namespace ILIAS\BackgroundTasks;

interface Observer
{

    /**
     * If the bucket goes into another state notify the observer.
     *
     * This also triggers a heartbeat.
     *
     * @param $state int
     *
     */
    public function notifyState($state);


    /**
     * You can change the progress of a currently running task.
     *
     * This also triggers a heartbeat.
     *
     * @param Task $task
     * @param int  $percentage
     *
     */
    public function notifyPercentage(Task $task, $percentage);


    /**
     * If the current task changes notify the observer.
     *
     * This also triggers a heartbeat.
     *
     * @param Task $task
     *
     */
    public function notifyCurrentTask(Task $task);


    /**
     * I'm still alive! If your calculation takes a really long time don't forget to use the heartbeat. Otherwise
     * the bucket might be killed while still running. All notify tasks of the observer also trigger a heartbeat.
     *
     * @return void
     */
    public function heartbeat();
}
