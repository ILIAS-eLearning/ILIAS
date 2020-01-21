<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface Worker
 *
 * @package ILIAS\BackgroundTasks
 *
 *                A Worker will go through background tasks and do some work on them. How much it
 *                does depends on the implementation.
 */
interface Worker
{

    /**
     * @return void
     */
    public function doWork();


    /**
     * Returns true iff the worker wants to be called within the current HTTP request. A
     * Synchronised worker will in fact not be a background task and stop only for user inputs.
     *
     * @return boolean
     */
    public function isSynchronised();
}
