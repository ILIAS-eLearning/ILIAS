<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Dependencies\Injector;
use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BasicScalarValueFactory;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\Value;

class BasicTaskFactory implements TaskFactory
{
    use BasicScalarValueFactory;
    /**
     * @var Injector
     */
    protected $injector;


    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }


    /**
     * @inheritdoc
     */
    public function createTask($class_name, $input = null)
    {
        if(!class_exists($class_name)) {
            require_once('./src/BackgroundTasks/Implementation/Tasks/NotFoundUserInteraction.php');
            return new NotFoundUserInteraction();
        }
        /** @var Task $task */
        $task = $this->injector->createInstance($class_name);
        if (!$task instanceof Task) {
            throw new InvalidArgumentException("The given classname $class_name is not a task.");
        }
        if ($input) {
            $wrappedInput = array_map(function ($i) {
                if ($i instanceof Task) {
                    return $i->getOutput();
                }
                if ($i instanceof Value) {
                    return $i;
                }

                return $this->wrapValue($i);
            }, $input);

            $task->setInput($wrappedInput);
        }

        return $task;
    }
}
