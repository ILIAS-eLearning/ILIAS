<?php

namespace ILIAS\BackgroundTasks;

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
