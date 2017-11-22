<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * Factory to build predicates for the fluent interface.
 *
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
class FluentPredicateFactory {
	/**
	 * @var	\Closure
	 */
	protected $continue;

	/**
	 * @var	\ILIAS\TMS\Filter\PredicateFactory
	 */
	protected $factory;

	public function __construct(\Closure $continue, \ILIAS\TMS\Filter\PredicateFactory $factory) {
		$this->continue = $continue;
		$this->factory = $factory;
	}

	// BASIC PREDICATES

	/**
	 * A predicate that always matches.
	 *
	 * @return	ilPredicate
	 */
	public function _TRUE() {
		$c = $this->continue;
		return $c($this->factory->_TRUE());
	}

	/**
	 * A predicate that never matches.
	 *
	 * @return	ilPredicate
	 */
	public function _FALSE() {
		$c = $this->continue;
		return $c($this->factory->_FALSE());
	}

	// TODO: ATOMS FOR BUILDING PREDICATES
}
