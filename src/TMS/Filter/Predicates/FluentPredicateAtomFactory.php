<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * Factory to build predicates for the fluent interface.
 *
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
class FluentPredicateAtomFactory {
	/**
	 * @var	\Closure
	 */
	protected $continue;

	/**
	 * @var	ilPredicateFactory
	 */
	protected $factory;

	public function __construct(\Closure $continue, \ILIAS\TMS\Filter\PredicateFactory $factory) {
		$this->continue = $continue;
		$this->factory = $factory;
	}

	public function int($value) {
		$c = $this->continue;
		return $c($this->factory->int($value));
	}

	public function str($value) {
		$c = $this->continue;
		return $c($this->factory->str($value));
	}

	public function date($value) {
		$c = $this->continue;
		return $c($this->factory->date($value));
	}

	public function field($name) {
		$c = $this->continue;
		return $c($this->factory->field($name));
	}
}
