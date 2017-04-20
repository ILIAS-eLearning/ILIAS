<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Result;
use ILIAS\Data;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class Error implements Data\Result {

	/**
	 * @var string | \Exception
	 */
	protected $value;

	public function __construct($value) {
		assert('is_string($value) || $value instanceof \Exception');
		$this->value = $value;
	}
	/**
 	 * @inheritdoc
	 */
	public function isOK() {
		return false;
	}

	/**
 	 * @inheritdoc
	 */
	public function value() {
		if($this->value instanceOf \Exception) {
			throw $this->value;
		}

		throw new Data\NotOKException($this->value);
	}

	/**
 	 * @inheritdoc
	 */
	public function isError() {
		return true;
	}

	/**
 	 * @inheritdoc
	 */
	public function error() {
		return $this->value;
	}

	/**
 	 * @inheritdoc
	 */
	public function valueOr($default) {
		return $default;
	}

	/**
 	 * @inheritdoc
	 */
	public function map(callable $f) {
		return $this;
	}

	/**
 	 * @inheritdoc
	 */
	public function then(callable $f) {
		return $this;
	}

	/**
 	 * @inheritdoc
	 */
	public function except(callable $f) {
		$result = $f($this->value);

		if($result === null) {
			return $this;
		}

		if(!$result instanceof Data\Result) {
			throw \UnexpectedValueException("The returned type of callable is not an instance of interface Result");
		}

		return $result;
	}
}
