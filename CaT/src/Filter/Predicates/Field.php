<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * An atom in the predicate that references a field.
 */
class Field extends ValueLike {
	/**
	 * @var	string
	 */
	protected $name;
	
	public function __construct(\CaT\Filter\PredicateFactory $factory, $name) {
		if (!is_string($name)) {
			throw new \InvalidArgumentException($err);
		}
		$this->name = $name;
		$this->setFactory($factory);
	}

	/**
	 * Get the name of the field.
	 *
	 * @return	string
	 */
	public function name() {
		return $this->name;
	}
}