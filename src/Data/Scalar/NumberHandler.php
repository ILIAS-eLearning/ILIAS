<?php

namespace ILIAS\Data\Scalar;

class NumberHandler implements Scalar {

	/**
	 * @var int
	 */
	public $value;


	/**
	 * NumberHandler constructor.
	 *
	 * @param int $value
	 */
	public function __construct(int $value) {
		$this->value = $value;
	}


	/**
	 * Returns the absolute value of number.
	 *
	 * @return float|int
	 */
	public function abs() {
		return abs($this->value);
	}


	/**
	 * Returns the next highest integer value by rounding up value if necessary.
	 *
	 * @return float
	 */
	public function ceil() {
		return ceil($this->value);
	}


	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function equals($value_comparator) {
		return $this->value === $value_comparator;
	}


	/**
	 * @return float
	 */
	public function floor() {
		return floor($this->value);
	}


	/**
	 * @return bool
	 */
	public function isFloat() {
		return is_float($this->value);
	}


	/**
	 * @return bool
	 */
	public function isInt() {
		return is_int($this->value);
	}


	/**
	 * @param $num
	 *
	 * @return float
	 */
	public function mod($num) {
		return floor($this->value - $num * ($this->value / $num));
	}


	/**
	 * @return float
	 */
	public function sqrt() {
		return sqrt($this->value);
	}


	/**
	 * @return array
	 */
	public function toArray() {
		return [ $this->value ];
	}


	/**
	 * @return float
	 */
	public function toFloat() {
		return (float)$this->value;
	}


	/**
	 * @return int
	 */
	public function toInt() {
		return intval($this->value);
	}


	public function toJSON() {
		return json_encode($this->value);
	}


	public function toString() {
		return (string)$this->value;
	}
}