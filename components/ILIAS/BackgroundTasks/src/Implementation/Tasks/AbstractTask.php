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

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Tasks\UserInteraction\UserInteractionOption;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BasicScalarValueFactory;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\UserInteraction\Option;
use ILIAS\BackgroundTasks\Types\Type;
use ILIAS\BackgroundTasks\Value;

/**
 * Class AbstractTask
 * @package ILIAS\BackgroundTasks\Implementation\Tasks
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 */
abstract class AbstractTask implements Task
{
    use BasicScalarValueFactory;

    public const MAIN_REMOVE = 'bt_main_remove';
    public const MAIN_ABORT = 'bt_main_abort';
    /**
     * @var Value[]
     */
    protected array $input = [];
    protected \ILIAS\BackgroundTasks\Value $output;

    /**
     * @param Value[]|Task[] $values
     */
    public function setInput(array $values): void
    {
        $this->input = $this->getValues($values);
        $this->checkTypes($this->input);
    }

    protected function checkTypes($values)
    {
        $expectedTypes = $this->getInputTypes();

        foreach ($expectedTypes as $i => $expectedType) {
            $givenType = $this->extractType($values[$i]);
            if (!$givenType->isExtensionOf($expectedType)) {
                throw new InvalidArgumentException("Types did not match when setting input for "
                    . static::class
                    . ". Expected type $expectedType given type $givenType.");
            }
        }
    }

    /**
     * @param $value Value|Task
     * @throws InvalidArgumentException
     */
    protected function extractType($value): Type
    {
        if (is_a($value, Value::class)) {
            return $value->getType();
        }
        if (is_a($value, Task::class)) {
            return $value->getOutputType();
        }

        throw new InvalidArgumentException("Input values must be Tasks or Values (extend BT\\Task or BT\\Value).");
    }

    /**
     * @return Value Returns a thunk value (yet to be calculated). It's used for task composition
     *               and type checks.
     */
    public function getOutput(): Value
    {
        $thunk = new ThunkValue($this->getOutputType());
        $thunk->setParentTask($this);

        return $thunk;
    }

    /**
     * @param $values (Value|Task)[]
     * @return Value[]
     */
    private function getValues($values): array
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
    public function getInput(): array
    {
        return $this->input;
    }

    public function getType(): string
    {
        return static::class;
    }

    /**
     * Unfold the task. If task A has dependency B and B' and B has dependency C, the resulting
     * list will be [A, B, C, B'].
     * @return Task[]
     */
    public function unfoldTask(): array
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
    public function getRemoveOption(): Option
    {
        return new UserInteractionOption('remove', self::MAIN_REMOVE);
    }

    /**
     * @inheritdoc
     */
    public function getAbortOption(): Option
    {
        return new UserInteractionOption('abort', self::MAIN_ABORT);
    }
}
