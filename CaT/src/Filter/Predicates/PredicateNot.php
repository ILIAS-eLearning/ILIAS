<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A predicate that is true when the other predicate is false.
 */
class PredicateNot extends Predicate {
	/**
	 * @var	ilPredicate
	 */
	protected $sub;

	public function __construct(\CaT\Filter\PredicateFactory $factory, Predicate $sub) {
		$this->setFactory($factory);
		$this->sub = $sub;
	}

	public function fields() {
		return $this->sub->fields();
	}

	/**
	 * Get the predicate without negation.
	 *
	 * @param	ilPredicate
	 */
	public function sub() {
		return $this->sub;
	}
}