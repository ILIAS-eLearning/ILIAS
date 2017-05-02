<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data\Factory;

class IsInt implements Constraint {
	const ERROR_MESSAGE = "The value %s is not an integer"

	/**
	 * @var ILIAS\Data\Factory
	 */
	protected $data_factory;

	/**
	 * @var callable
	 */
	protected $builder = null;

	public function __construct(Factory $data_factory) {
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
		return is_int($value);
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

	protected function getErrorMessage($value) {
		if($this->builder !== null) {
			return $this->builder($value);
		}

		return sprintf(self::ERROR_MESSAGE, $value);
	}
}