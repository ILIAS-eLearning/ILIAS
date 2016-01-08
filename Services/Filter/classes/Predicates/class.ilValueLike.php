<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

/**
 * An atom in a predicate that acts like a value.
 */
class ilValueLike {
	/**
	 * @var	ilPredicateFactory
	 */
	protected $factory;

	protected function setFactory(ilPredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function fluent_factory(\Closure $continue) {
		require_once("Services/Filter/classes/Predicates/class.ilFluentPredicateAtomFactory.php");
		return new ilFluentPredicateAtomFactory($continue, $this->factory);
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function EQ(ilValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->EQ($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ilValueLike $value) use ($self) {
			return $self->EQ($value);
		});
	}

	/**
	 * @param	Predicate|null	$other
	 * @return	Predicate
	 */
	public function LE(ilValueLike $other = null) {
		if ($other !== null) {
			return $this->factory->LE($this, $other);
		}

		$self = $this;
		return $this->fluent_factory(function(ilValueLike $value) use ($self) {
			return $self->LE($value);
		});
	}

	// TODO:
	//		- NEQ
	//		- LT
	//		- GE
	//		- GT

}