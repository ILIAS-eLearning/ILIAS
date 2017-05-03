<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;

class LessThan implements Constraint {
	const ERROR_MESSAGE = "The checked value is greater than.";

	/**
	 * @var ILIAS\Data\Factory
	 */
	protected $data_factory;

	/**
	 * @var int
	 */
	protected $max;

	/**
	 * @var callable
	 */
	protected $builder = null;

	public function __construct($max, Factory $data_factory) {
		assert('is_int($max)');
		$this->max = $max;
		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function check($value) {
		if(!$this->accepts($value)) {
			throw new \UnexpectedValueException($this->getErrorMessage($value));
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function accepts($value) {
		return $value < $this->max;
	}

	/**
	 * @inheritdoc
	 */
	public function problemWith($value) {
		if(!$this->accepts($value)) {
			return $this->getErrorMessage($value);
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function restrict(Result $result) {
		if($result->isOk() && ($problem = $this->problemWith($result->value())) !== null) {
			$error = $this->data_factory->error($problem);
			return $error;
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function withProblemBuilder(callable $builder) {
		$clone = clone $this;
		$clone->builder = $builder;
		return $clone;
	}

	/**
	 * Get the problem message
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		if($this->builder !== null) {
			return call_user_func($this->builder);
		}

		return self::ERROR_MESSAGE;
	}
}