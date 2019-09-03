<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Values\PrimitiveValueWrapperFactory;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BasicScalarValueFactory;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

/**
 * Class AbstractTask
 *
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class AbstractTask implements Task
{

    use BasicScalarValueFactory;
    const MAIN_REMOVE = 'bt_main_remove';
    const MAIN_ABORT = 'bt_main_abort';
    /**
     * @var Value[]
     */
    protected $input = [];
    /**
     * @var Value
     */
    protected $output;


    /**
     * @param $values (Value|Task)[]
     *
     * @return void
     */
    public function setInput(array $values)
    {
        $this->input = $this->getValues($values);
        $this->checkTypes($this->input);
    }


    protected function checkTypes($values)
    {
        $expectedTypes = $this->getInputTypes();

        for ($i = 0; $i < count($expectedTypes); $i++) {
            $expectedType = $expectedTypes[$i];
            $givenType = $this->extractType($values[$i]);
            if (!$givenType->isExtensionOf($expectedType)) {
                throw new InvalidArgumentException("Types did not match when setting input for "
                    . get_called_class()
                    . ". Expected type $expectedType given type $givenType.");
            }
        }
    }


    /**
     * @param $value Value
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function extractType($value)
    {
        if (is_a($value, Value::class)) {
            return $value->getType();
        }
        if (is_a($value, Task::class)) {
            ;
        }

        return $value->getOutputType();

        throw new InvalidArgumentException("Input values must be tasks or Values (extend BT\\Task or BT\\Value).");
    }


    /**
     * @return Value Returns a thunk value (yet to be calculated). It's used for task composition
     *               and type checks.
     *
     */
    public function getOutput()
    {
        $thunk = new ThunkValue($this->getOutputType());
        $thunk->setParentTask($this);

        return $thunk;
    }


    /**
     * @param $values (Value|Task)[]
     *
     * @return Value[]
     */
    private function getValues($values)
    {
        $inputs = [];

        foreach ($values as $value) {
            if ($value instanceof Task) {
                $inputs[] = $value->getOutput();
            } elseif ($value instanceof Value) {
                $inputs[] = $value;
            } else {
                $inputs[] = $this->wrapScalar($value);
            }
        }

        return $inputs;
    }


    /**
     * @return Value[]
     */
    public function getInput()
    {
        return $this->input;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return get_called_class();
    }


    /**
     * Unfold the task. If task A has dependency B and B' and B has dependency C, the resulting
     * list will be [A, B, C, B'].
     *
     * @return Task[]
     */
    public function unfoldTask()
    {
        $list = [$this];
        foreach ($this->getInput() as $input) {
            if (is_a($input, ThunkValue::class)) {
                $list = array_merge($list, $input->getParentTask()->unfoldTask());
            }
        }

        return $list;
    }


    /**
     * @inheritdoc
     */
    public function getRemoveOption()
    {
        return new UserInteractionOption('remove', self::MAIN_REMOVE);
    }


    /**
     * @inheritdoc
     */
    public function getAbortOption()
    {
        return new UserInteractionOption('abort', self::MAIN_ABORT);
    }
}