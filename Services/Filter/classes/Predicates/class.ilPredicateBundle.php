<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilPredicate.php");

/**
 * A bundle of some predicates.
 */
abstract class ilPredicateBundle extends ilPredicate {
	/**
	 * @var	ilPredicate[]
	 */
	protected $subs;

	public function __construct(ilPredicateFactory $factory, array $subs) {
		$this->subs = array_map(function(ilPredicate $p) {
			return $p;
		}, $subs);

		$this->setFactory($factory);
	}

	public function fields() {
		return array();
	}

	/**
	 * Get the bundled predicates a array.
	 *
	 * @return	ilPredicate[]
	 */
	public function subs() {
		return array_merge($this->subs, array());
	}
}