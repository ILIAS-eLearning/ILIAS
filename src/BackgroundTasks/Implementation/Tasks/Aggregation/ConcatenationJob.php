<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks\Aggregation;


use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\AggregationValues\ListValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\ListType;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\SingleType;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\ValueType;

class ConcatenationJob extends AbstractJob {

	/**
	 * @param \ILIAS\BackgroundTasks\Value[] $input
	 * @param Observer $observer Notify the observer about your progress!
	 * @return StringValue
	 */
	public function run(Array $input, Observer $observer) {
		/** @var ScalarValue[] $list */
		$list = $input[0]->getList();
		/** @var ScalarValue[] $values */
		$values = array_map(
			function($a) { return $a->getValue(); },
			$list);

		return new StringValue(implode(', ', $values));
	}

	/**
	 * @return bool returns true iff the job's output ONLY depends on the input. Stateless task results may be cached!
	 */
	public function isStateless() {
		return true;
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
		return [new ListType(ScalarValue::class)];
	}

	/**
	 * @return ValueType
	 */
	public function getOutputType() {
		return new SingleType(StringValue::class);
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
		return 0;
	}
}