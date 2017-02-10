<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks;

use ILIAS\BackgroundTasks\Implementation\Observer\ObserverMock;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\SingleType;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\ValueType;

class PlusJob extends AbstractJob {

	/**
	 * PlusJob constructor.
	 *
	 * Jobs dependencies will be injected. Type hinting is necessary for that!
	 *
	 * @param Observer $mock The mock will be injected by the dependency injection.
	 */
	public function __construct(Observer $mock) {
	}

	/**
	 * @return string
	 */
	public function getId() {
		return get_called_class();
	}

	/**
	 * @return ValueType[] Class-Name of the IO
	 */
	public function getInputTypes() {
		return [
			new SingleType(IntegerValue::class),
			new SingleType(IntegerValue::class)
		];
	}

	/**
	 * @return ValueType
	 */
	public function getOutputType() {
		return new SingleType(IntegerValue::class);
	}

	/**
	 * @return bool Returns true iff the job supports giving feedback about the percentage done.
	 */
	public function supportsPercentage() {
		return false;
	}

	/**
	 * @return int Returns 0 if !supportsPercentage and the percentage otherwise.
	 */
	public function getPercentage() {
		return false;
	}

	/**
	 * @param Value[] $input
	 * @param Observer $observer Notify the observer about your progress!
	 * @return Value
	 */
	public function run(Array $input, Observer $observer) {
		/** @var IntegerValue $a */
		$a = $input[0];
		/** @var IntegerValue $b */
		$b = $input[1];

		return new IntegerValue($a->getValue() + $b->getValue());
	}

	/**
	 * @return bool returns true iff the job's output ONLY depends on the input. Stateless task results may be cached!
	 */
	public function isStateless() {
		return true;
	}
}