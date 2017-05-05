<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;
use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

class Custom implements Constraint {
	/**
	 * @var ILIAS\Data\Factory
	 */
	protected $data_factory;

	/**
	 * @var callable
	 */
	protected $is_ok;

	/**
	 * @var callable
	 */
	protected $error;

	/**
	 * @param string|callable   $error
	 */
	public function __construct(callable $is_ok, $error, Data\Factory $data_factory) {
		$this->is_ok = $is_ok;

		if(!is_callable($error)) {
			$this->error = function() use ($error) { return $error; };
		} else {
			$this->error = $error;
		}

		$this->data_factory = $data_factory;
	}

	/**
	 * @inheritdoc
	 */
	final public function check($value) {
		if(!$this->accepts($value)) {
			throw new \UnexpectedValueException($this->getErrorMessage($value));
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	final public function accepts($value) {
		return call_user_func($this->is_ok, $value);
	}

	/**
	 * @inheritdoc
	 */
	final public function problemWith($value) {
		if(!$this->accepts($value)) {
			return $this->getErrorMessage($value);
		}

		return null;
	}

	/**
	 * @inheritdoc
	 */
	final public function restrict(Result $result) {
		if($result->isError()) {
			return $result;
		}

		$problem = $this->problemWith($result->value());
		if($problem !== null) {
			$error = $this->data_factory->error($problem);
			return $error;
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	final public function withProblemBuilder(callable $builder) {
		$clone = clone $this;
		$clone->error = $builder;
		return $clone;
	}

	/**
	 * Get the problem message
	 *
	 * @return string
	 */
	final public function getErrorMessage($value) {
		return call_user_func($this->error, $value);
	}
}