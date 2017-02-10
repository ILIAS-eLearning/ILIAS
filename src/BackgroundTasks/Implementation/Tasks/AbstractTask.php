<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\PrimitiveValueWrapperFactory;
use ILIAS\BackgroundTasks\Implementation\Values\ThunkValue;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Value;

abstract class AbstractTask implements Task {

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
	 * @return void
	 */
	public function setInput($values) {
		$this->input = $this->getValues($values);
		$this->checkTypes($this->input);
	}

	protected function checkTypes($values) {
		$expectedTypes = $this->getInputTypes();

		for ($i = 0; $i < count($expectedTypes); $i++ ) {
			$expectedType = $expectedTypes[$i];
			$givenType = $this->extractType($values[$i]);
			if(!$givenType->isSubtypeOf($expectedType))
				throw new InvalidArgumentException("Types did not match when setting input for " . get_class() . ". Expected type $expectedType given type $givenType.");
		}
	}

	protected function extractType($value) {
		if (is_a($value, Value::class))
			return $value->getType();
		if (is_a($value, Task::class));
			return $value->getOutputType();

		throw new InvalidArgumentException("Input values must be tasks or Values (extend BT\\Task or BT\\Value).");
	}

	/**
	 * @return Value Returns a thunk value (yet to be calculated). It's used for task composition and type checks.
	 *
	 */
	public function getOutput() {
		$thunk = new ThunkValue($this->getOutputType());
		$thunk->setParentTask($this);
		return $thunk;
	}

	/**
	 * @param $values (Value|Task)[]
	 * @return Value[]
	 */
	private function getValues($values) {
		$wrapper = PrimitiveValueWrapperFactory::getInstance();
		$inputs = [];

		foreach($values as $value) {
			if($value instanceof Task)
				$inputs[] = $value->getOutput();
			elseif($value instanceof Value)
				$inputs[] = $value;
			else
				$inputs[] = $wrapper->wrapValue($value);

		}
		return $inputs;
	}

	/**
	 * @return Value[]
	 */
	public function getInput() {
		return $this->input;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return get_called_class();
	}
}