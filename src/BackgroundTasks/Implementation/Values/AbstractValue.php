<?php

namespace ILIAS\BackgroundTasks\Implementation\Values;

use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class AbstractValue
 *
 * @package ILIAS\BackgroundTasks\Implementation\Values
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class AbstractValue implements Value
{

    /**
     * @var Task
     */
    protected $parentTask;


    /**
     * @return Type
     */
    public function getType()
    {
        return new SingleType(get_called_class());
    }


    /**
     * @return Task
     */
    public function getParentTask()
    {
        return $this->parentTask;
    }


    /**
     * @param Task $parentTask
     *
     * @return void
     */
    public function setParentTask(Task $parentTask)
    {
        $this->parentTask = $parentTask;
    }


    /**
     * @return bool
     */
    public function hasParentTask()
    {
        return isset($this->parentTask);
    }
}