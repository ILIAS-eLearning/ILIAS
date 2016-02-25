<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * An atom in a predicate that is a value.
 */
class ValueList {
	/**
	 * @var	\CaT\Filter\PredicateFactory
	 */
	protected $factory;
	protected $values;

	public function __construct( array $values) {
		$this->values = $values; 
	}

	/**
	 * Get the value.
	 *
	 * @return	mixed
	 */
	public function values() {
		return $this->values;
	}
}