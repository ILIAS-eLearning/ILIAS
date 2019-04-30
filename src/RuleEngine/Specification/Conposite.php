<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Class Composite
 *
 * @package ILIAS\RuleEngine\Specification
 */
class Composite implements Specification {

	/**
	 * @var string
	 */
	private $operator;
	/**
	 * @var array
	 */
	private $specifications = [];


	/**
	 * Builds a composite specification.
	 *
	 * @param string $operator       The operator used to join the specifications.
	 * @param array  $specifications A list specifications to combine.
	 */
	public function __construct($operator, array $specifications = []) {
		$this->operator = $operator;
		if (empty($specifications)) {
			throw new \LogicException('No specifications given.');
		}
		foreach ($specifications as $specification) {
			$this->addSpecification($specification);
		}
	}


	private function addSpecification(Specification $specification) {
		$this->specifications[] = $specification;
	}


	public function getKey() {
		// TODO: Implement getKey() method.
	}


	public function getValue() {
		// TODO: Implement getValue() method.
	}


	public function getOperator() {
		return $this->operator;
	}
}