<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

interface TaskFactory
{
    
    /**
     * @param $class_name   string  The fully quallified classname to create
     * @param $input        Value[]|null The values you want as inputs to the task.
     */
    public function createTask(string $class_name, ?array $input = null) : Task;
}
