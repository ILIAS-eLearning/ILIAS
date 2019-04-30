<?php

namespace ILIAS\RuleEngine\Specification;

use ILIAS\Data\Scalar\Scalar;

/**
 * Class GenericSpecification
 *
 * @package ILIAS\RuleEngine\Specification
 */
class GenericSpecification implements Specification {

	/**
	 * @var Scalar
	 */
	private $key;
	/**
	 * @var Scalar
	 */
	private $value;
	private $operator;


	public function __construct($key, $value, $operator) {
		$this->key = $key;
		$this->value = $value;
		$this->operator = $operator;
	}


	/**
	 * @return Scalar
	 */
	public function getKey() {
		return $this->key;
	}


	/**
	 * @return Scalar
	 */
	public function getValue() {
		return $this->value;
	}


	/**
	 * @inheritDoc
	 */
	public function getOperator() {
		return $this->operator;
	}
}