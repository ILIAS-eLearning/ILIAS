<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data\Factory;
use ILIAS\Data\Result;

class Sequential implements Constraint {
	/**
	 * @var ILIAS\Data\Factory
	 */
	protected $data_factory;

	/**
	 * @var Constraint[]
	 */
	protected $constraints;

	/**
	 * @var Constraint
	 */
	protected $failed_constraint;

	/**
	 * @var callable
	 */
	protected $builder = null;

	public function __construct(array $constraints, Factory $data_factory) {
		$this->min = $min;
		$this->data_factory = $data_factory;
		$this->constraints = $constraints;
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
		foreach ($this->constraints as $key => $constraint) {
			if(!$constraint->accepts($value)) {
				$this->failed_constraint = $constraint;
				return false;
			}
		}

		return true;
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

		return $this->failed_constraint->getErrorMessage();
	}
}