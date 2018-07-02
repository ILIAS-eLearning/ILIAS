<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * An atom in a predicate that is a value.
 */
abstract class Value extends ValueLike {
	/**
	 * @var mixed
	 */
	protected $value;


	public function __construct(\ILIAS\TMS\Filter\PredicateFactory $factory, $value) {
		$err = $this->value_errors($value);
		if ($err) {
			throw new \InvalidArgumentException($err);
		}
		$this->value = $value;
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
