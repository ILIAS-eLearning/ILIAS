<?php

namespace ILIAS\RuleEngine\Specification;

/**
 * Base class for specifications. Only provides a few usefull shortcuts.
 */
abstract class AbstractSpecification implements Specification {

	/**
	 * @param Specification $spec
	 *
	 * @return AndXSpecification
	 */
	public function andX(Specification $spec): AndXSpecification {
		return new AndXSpecification([ $this, $spec ]);
	}


	/**
	 * @param Specification $spec
	 *
	 * @return OrXSpecification
	 */
	public function orX(Specification $spec): OrXSpecification {
		return new OrXSpecification([ $this, $spec ]);
	}


	/**
	 * @return NotSpecification
	 */
	public function not(): NotSpecification {
		return new NotSpecification($this);
	}


	/**
	 * @return array
	 */
	public function getParameters(): array {
		return [];
	}
}