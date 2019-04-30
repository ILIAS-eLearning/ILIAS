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


	/**
	 * {@inheritdoc}
	 */
	public function getRule(): string {
		return implode(sprintf(' %s ', $this->operator), array_map(function (Specification $specification) {
			return sprintf('(%s)', $specification->getRule());
		}, $this->specifications));
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameters(): array {
		//TODO
	}
}