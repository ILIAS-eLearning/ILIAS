<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilPredicate.php");

/**
 * A predicate to compare two values.
 */
abstract class ilPredicateComparison extends ilPredicate {
	/**
	 * @var ilValueLike
	 */
	protected $l;
	
	/**
	 * @var ilValueLike
	 */
	protected $r;

	public function __construct(ilPredicateFactory $factory, ilValueLike $l, ilValueLike $r) {
		$this->l = $l;
		$this->r = $r;
		$this->setFactory($factory);
	}

	/**
	 * Get the left side of the comparison.
	 *
	 * @return	ilValueLike
	 */
	public function left() {
		return $this->l;
	}

	/**
	 * Get the right side of the comparison.
	 *
	 * @return	ilValueLike
	 */
	public function right() {
		return $this->r;
	}
}