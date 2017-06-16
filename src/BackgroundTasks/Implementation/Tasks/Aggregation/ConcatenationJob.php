<?php

namespace ILIAS\BackgroundTasks\Implementation\Tasks\Aggregation;


use ILIAS\BackgroundTasks\Implementation\Tasks\AbstractJob;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\ValueType;
use ILIAS\BackgroundTasks\Types\ListType;
use ILIAS\BackgroundTasks\Types\SingleType;
use ILIAS\BackgroundTasks\Types\Type;

class ConcatenationJob extends AbstractJob {

	/**
	 * @param \ILIAS\BackgroundTasks\Value[] $input
	 * @param Observer                         $observer Notify the bucket about your progress!
	 *
*@return StringValue
	 */
	public function run(Array $input, Observer $observer) {
		/** @var ScalarValue[] $list */
		$list = $input[0]->getList();
		/** @var ScalarValue[] $values */
		$values = array_map(
			function($a) { return $a->getValue(); },
			$list);

		$string_value = new StringValue();
		$string_value->setValue(implode(', ', $values));

		return $string_value;
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
	 * @return Type[] Class-Name of the IO
	 */
	public function getInputTypes() {
		return [new ListType(ScalarValue::class)];
	}

	/**
	 * @return Type
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