<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A predicate that is always true.
 */
class PredicateTrue extends Predicate {
	public function __construct(\CaT\Filter\PredicateFactory $factory) {
		$this->setFactory($factory);
	}

	public function fields() {
		return array();
	}
}