<?php

use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\ValueTypes\SingleType;
use ILIAS\BackgroundTasks\Task\Option;
use ILIAS\BackgroundTasks\Value;
use ILIAS\BackgroundTasks\Observer;
use ILIAS\BackgroundTasks\ValueType;

class DownloadInteger extends AbstractUserInteraction {
	/**
	 * @param Value $input The input value of this task.
	 * @return Option[] Options are buttons the user can press on this interaction.
	 */
	public function getOptions(Value $input) {
		return [
			new UserInteractionOption("download", "download"),
			new UserInteractionOption("dismiss", "dismiss")
		];
	}

	/**
	 * @param array $input The input value of this task.
	 * @param Option $user_selected_option The Option the user chose.
	 * @param Observer $observer Notify the observer about your progress!
	 * @return Value
	 */
	public function interaction(Array $input, Option $user_selected_option, Observer $observer) {
		/** @var IntegerValue $a */
		$integerValue = $input[0];

		if($user_selected_option->getValue() == "download") {
			$outputter = new ilPHPOutputDelivery();
			$outputter->start("IntegerFile");
			echo $integerValue->getValue();
			$outputter->stop();
		}

		return VoidValue::instance();
	}

	/**
	 * @return ValueType[] Class-Name of the IO
	 */
	public function getInputTypes() {
		return [
			new SingleType(IntegerValue::class)
		];
	}

	/**
	 * @return ValueType
	 */
	public function getOutputType() {
		return VoidValue::instance();
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