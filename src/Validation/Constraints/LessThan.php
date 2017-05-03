<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;

class LessThan extends Custom implements Constraint {
	const ERROR_MESSAGE = "The checked value is greater than.";

	/**
	 * @var int
	 */
	protected $max;

	public function __construct($max, Factory $data_factory) {
		assert('is_int($max)');
		$this->max = $max;
		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		return $value < $this->max;
	}

	/**
	 * Get the problem message
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		if($this->error !== null) {
			return call_user_func($this->error);
		}

		return self::ERROR_MESSAGE;
	}
}