<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilValueLike.php");

/**
 * An atom in a predicate that is a value.
 */
abstract class ilValue extends ilValueLike {
	/**
	 * @var mixed
	 */
	protected $value;


	public function __construct(ilPredicateFactory $factory, $value) {
		$err = $this->value_errors($value);
		if ($err) {
			throw new \InvalidArgumentException($err);
		}
		$this->value = $value;#
		$this->setFactory($factory);
	}

	/**
	 * Get the value.
	 *
	 * @return	mixed
	 */
	public function value() {
		return $this->value;
	}

	/**
	 * Check the inserted value.
	 *
	 * @param	mixed		$value
	 * @return	str|null			Return string with error message or null
	 *								if value is ok.
	 */
	abstract protected function value_errors($value);
}