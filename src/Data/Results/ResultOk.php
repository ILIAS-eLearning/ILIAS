<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Results;
use ILIAS\Data\Result;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ResultOk implements Result {

	/**
	 * @var mixed | \Exception
	 */
	protected $value;

	public function __construct($value) {
		$this->value = $value;
	}

	/**
	 * @inheritdoc
	 */
	public function isOK() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function value() {
		return $this->value;
	}

	/**
	 * @inheritdoc
	 */
	public function isError() {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function error() {
		throw new \LogicException("This is a OK result. No error message available");
	}

	/**
	 * @inheritdoc
	 */
	public function valueOr($default) {
		return $this->value;
	}

	/**
	 * @inheritdoc
	 */
	public function map(callable $f) {
		$clone = clone $this;
		$value = $f($this->value);
		$clone->value = $value;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function then(callable $f) {
		$clone = clone $this;
		$result = $f($this->value);

		if($this->isError() || $result === null) {
			return $clone;
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function except(callable $f) {
		return clone $this;
	}
}
