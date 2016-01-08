<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilPredicate.php");

/**
 * A predicate that is always true.
 */
class ilPredicateTrue extends ilPredicate {
	public function __construct(ilPredicateFactory $factory) {
		$this->setFactory($factory);
	}
}