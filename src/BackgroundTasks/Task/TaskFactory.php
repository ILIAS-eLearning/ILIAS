<?php

namespace ILIAS\BackgroundTasks\Task;

use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

interface TaskFactory
{

    /**
     * @param $class_name   string  The fully quallified classname to create
     * @param $input        Value[] The values you want as inputs to the task.
     *
     * @return Task
     */
    public function createTask($class_name, $input = null);
}