<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BasicScalarValueFactory;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValueFactory;
use ILIAS\BackgroundTasks\Task;
use ILIAS\BackgroundTasks\Task\TaskFactory;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Dependencies\Injector;

class BasicTaskFactory implements TaskFactory {

	use BasicScalarValueFactory;
	/**
	 * @var Injector
	 */
	protected $injector;


	public function __construct(Injector $injector) {
		$this->injector = $injector;
	}


	/**
	 * @inheritdoc
	 */
	public function createTask($class_name, $input = null) {
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