<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A bundle of some predicates.
 */
abstract class PredicateBundle extends Predicate {
	/**
	 * @var	Predicate[]
	 */
	protected $subs;

	public function __construct(\CaT\Filter\PredicateFactory $factory, array $subs) {
		$this->subs = array_map(function(Predicate $p) {
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