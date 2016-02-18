<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
abstract class Predicate {
	/**
	 * @var	ilPredicateFactory
	 */
	protected $factory;

	protected function setFactory(\CaT\Filter\PredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function fluent_factory(\Closure $continue) {
		return new FluentPredicateFactory($continue, $this->factory);
	}

	/**
	 * Get all fields that are used in this predicate.
	 *
	 * @return	ilField[]
	 */
	abstract public function fields();

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function _OR(Predicate $other = null) {
		if ($other !== null) {
			return $this->factory->_OR($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(Predicate $pred) use ($self) {
			return $self->_OR($pred);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function _AND(Predicate $other = null) {
		if ($other !== null) {
			return $this->factory->_AND($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(Predicate $pred) use ($self) {
			return $self->_AND($pred);
		});
	}
}