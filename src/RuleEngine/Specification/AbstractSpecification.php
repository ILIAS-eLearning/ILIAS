<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Base class for specifications. Only provides a few usefull shortcuts.
 */
abstract class AbstractSpecification implements Specification {

	/**
	 * Create a conjunction with the current specification and another one.
	 *
	 * @param Specification $spec The other specification.
	 */
	public function andX(Specification $spec): AndXSpecification {
		return new AndXSpecification([ $this, $spec ]);
	}


	/**
	 * Create a disjunction with the current specification and another one.
	 *
	 * @param Specification $spec The other specification.
	 */
	public function orX(Specification $spec): OrXSpecification {
		return new OrXSpecification([ $this, $spec ]);
	}


	/**
	 * Negate the current specification.
	 *
	 * @return Not
	 */
	public function not(): NotSpecification {
		return new NotSpecification($this);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameters(): array {
		return [];
	}
}