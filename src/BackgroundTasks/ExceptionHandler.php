<?php

namespace ILIAS\BackgroundTasks;

/**
 * Interface ExceptionHandler
 * @package ILIAS\BackgroundTasks
 *          Use custom ExceptionHandlers for your Buckets to handle exceptions in your tasks
 *          gracefully
 */
interface ExceptionHandler
{
    
    /**
     * When working on a bucket and an exception occurs the exception handler will try to end the
     * bucket operation gracefully.
     * @param Task|null            $task
     */
    public function handleException(Exceptions\Exception $exception, Bucket $bucket, Task $task = null) : void;
}
