<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilPredicate.php");

/**
 * A predicate that is true when the other predicate is false.
 */
class ilPredicateNot extends ilPredicate {
	/**
	 * @var	ilPredicate
	 */
	protected $sub;

	public function __construct(ilPredicateFactory $factory, ilPredicate $sub) {
		$this->setFactory($factory);
		$this->sub = $sub;
	}

	public function fields() {
		return array();
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