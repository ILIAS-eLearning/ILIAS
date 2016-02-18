<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * An atom in a predicate that acts like a value.
 */
class ValueLike {
	/**
	 * @var	\CaT\Filter\PredicateFactory
	 */
	protected $factory;

	protected function setFactory(\CaT\Filter\PredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function fluent_factory(\Closure $continue) {
		return new FluentPredicateAtomFactory($continue, $this->factory);
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function EQ(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->EQ($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->EQ($value);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function LE(ValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->LE($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ValueLike $value) use ($self) {
			return $self->LE($value);
		});
	}

	// TODO:
	//		- NEQ
	//		- LT
	//		- GE
	//		- GT

}