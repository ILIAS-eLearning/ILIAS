<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

/**
 * A predicate is some abstract function from some record (like a dictionary,
 * a row in a table) to true or false.
 */
abstract class ilPredicate {
	/**
	 * @var	ilPredicateFactory
	 */
	protected $factory;

	protected function setFactory(ilPredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function fluent_factory(\Closure $continue) {
		require_once("Services/Filter/classes/Predicates/class.ilFluentPredicateFactory.php");
		return new ilFluentPredicateFactory($continue, $this->factory);
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function _OR(ilPredicate $other = null) {
		if ($other !== null) {
			return $this->factory->_OR($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ilPredicate $pred) use ($self) {
			return $self->_OR($pred);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function _AND(ilPredicate $other = null) {
		if ($other !== null) {
			return $this->factory->_AND($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ilPredicate $pred) use ($self) {
			return $self->_AND($pred);
		});
	}
}