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

	public function __construct(\CaT\Filter\PredicateFactory $factory, $values) {
		$this->factory = $factory;
	}

	/**
	 * Get the value.
	 *
	 * @return	mixed
	 */
	public function values() {
		return array();
	}
}