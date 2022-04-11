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
    
    protected \ILIAS\BackgroundTasks\Dependencies\Injector $injector;
    
    public function __construct(Injector $injector)
    {
        $this->injector = $injector;
    }
    
    /**
     * @inheritdoc
     */
    public function createTask(string $class_name, ?array $input = null) : Task
    {
        if (!class_exists($class_name)) {
            return new NotFoundUserInteraction();
        }
        /** @var Task $task */
        $task = $this->injector->createInstance($class_name);
        if ($input) {
            $wrappedInput = array_map(function ($i) : \ILIAS\BackgroundTasks\Value {
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
