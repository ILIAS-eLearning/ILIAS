<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * A predicate that is always true.
 */
class PredicateTrue extends Predicate {
	public function __construct(\ILIAS\TMS\Filter\PredicateFactory $factory) {
		$this->setFactory($factory);
	}

	public function fields() {
		return array();
	}
}
