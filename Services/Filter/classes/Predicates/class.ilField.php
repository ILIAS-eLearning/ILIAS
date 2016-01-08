<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilValueLike.php");

/**
 * An atom in the predicate that references a field.
 */
class ilField extends ilValueLike {
	/**
	 * @var	string
	 */
	protected $name;
	
	public function __construct(ilPredicateFactory $factory, $name) {
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
		return $this->value;
	}
}