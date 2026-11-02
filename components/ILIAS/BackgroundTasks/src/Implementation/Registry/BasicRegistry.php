<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
declare(strict_types=1);

namespace ILIAS\BackgroundTasks\Implementation\Registry;

use ILIAS\BackgroundTasks\Registry\Registry;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

class BasicRegistry implements Registry
{
    /**
     * @var array<string, Task>
     */
    private array $tasks = [];

    /**
     * @var array<string, Value>
     */
    private array $values = [];

    /**
     * @param Task[]  $tasks
     * @param Value[] $values
     */
    public function __construct(array $tasks, array $values)
    {
        foreach ($tasks as $task) {
            $this->tasks[$this->unique($task)] = $task;
        }
        foreach ($values as $value) {
            $this->values[$this->unique($value)] = $value;
        }
    }

    public function isRegistered(string $class_name): bool
    {
        $class_name = ltrim($class_name, '\\');
        return isset($this->tasks[$class_name]) || isset($this->values[$class_name]);
    }

    public function newTask(string $class_name): Task
    {
        $class_name = ltrim($class_name, '\\');
        if (!isset($this->tasks[$class_name])) {
            throw new \OutOfBoundsException("No task $class_name is registered.");
        }
        return clone $this->tasks[$class_name];
    }

    public function newValue(string $class_name): Value
    {
        $class_name = ltrim($class_name, '\\');
        if (!isset($this->values[$class_name])) {
            throw new \OutOfBoundsException("No value $class_name is registered.");
        }
        return clone $this->values[$class_name];
    }

    private function unique(Task|Value $prototype): string
    {
        $class_name = $prototype::class;
        if (isset($this->tasks[$class_name]) || isset($this->values[$class_name])) {
            throw new \LogicException("$class_name is contributed more than once.");
        }
        return $class_name;
    }
}
