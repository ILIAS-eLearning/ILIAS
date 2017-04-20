<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ResultImpl implements Result {

	/**
	 * @var mixed | \Exception
	 */
	protected $value;

	/**
	 * @var boolean
	 */
	protected $is_ok;

	public function __construct($value, $is_ok = true) {
		$this->value = $value;
		$this->is_ok = $is_ok;
	}
	/**
 	 * @inheritdoc
	 */
	public function isOK() {
		return $this->is_ok;
	}

	/**
 	 * @inheritdoc
	 */
	public function value() {
		if($this->isError() && $this->value instanceOf \Exception) {
			throw $this->value;
		} else if($this->isError()) {
			throw new NotOKException($this->value);
		}

		return $this->value;
	}

	/**
 	 * @inheritdoc
	 */
	public function isError() {
		return $this->is_ok === false;
	}

	/**
 	 * @inheritdoc
	 */
	public function error() {
		if($this->isOk()) {
			throw new \LogicException("");
		}

		return $this->value;
	}

	/**
 	 * @inheritdoc
	 */
	public function valueOr($default) {
		if($this->isError()) {
			return $default;
		}

		return $this->value;
	}

	/**
 	 * @inheritdoc
	 */
	public function map(callable $f) {
		$clone = clone $this;

		if($this->isError()) {
			return $clone;
		}

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
		$clone = clone $this;
		$result = $f($this->value);

		if($this->isOk() || $result === null) {
			return $clone;
		}

		return $result;
	}
}
