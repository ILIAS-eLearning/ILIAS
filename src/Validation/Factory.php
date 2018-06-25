<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation;
use ILIAS\Data;
/**
 * Factory for creating constraints.
 */
class Factory {
	/**
	 * @var ILIAS\Data\Factory
	 */
	protected $data_factory;

	public function __construct(Data\Factory $data_factory) {
		$this->data_factory = $data_factory;
	}


	// COMBINATORS

	/**
	 * Get a constraint that sequentially checks the supplied constraints.
	 *
	 * The new constraint tells the problem of the first violated constraint.
	 *
	 * @param   Constraint[]   $others
	 * @return  Constraint
	 */
	public function sequential(array $others) {
		return new Constraints\Sequential($others, $this->data_factory);
	}

	/**
	 * Get a constraint that checks the supplied constraints in parallel.
	 *
	 * The new constraint tells the problems of all violated constraints.
	 *
	 * @param   Constraint[]   $others
	 * @return	Constraint
	 */
	public function parallel(array $others) {
		return new Constraints\Parallel($others, $this->data_factory);
	}

	/**
	 * Get a negated constraint.
	 *
	 * @param   Constraint   $other
	 * @return  Constraint
	 */
	public function not(Constraint $other) {
		return new Constraints\Not($other, $this->data_factory);
	}

	// SOME RESTRICTOINS

	/**
	 * Get a constraint for an integer.
	 *
	 * @return  Constraint
	 */
	public function isInt() {
		return new Constraints\IsInt($this->data_factory);
	}

	/**
	 * Get the constraint that some value is larger than $min.
	 *
	 * @param   int   $min
	 * @return  Constraint
	 */
	public function greaterThan($min) {
		return new Constraints\GreaterThan($min, $this->data_factory);
	}

	/**
	 * Get the constraint that some value is smaller then $max.
	 *
	 * @param   int   $max
	 * @return  Constraint
	 */
	public function lessThan($max) {
		return new Constraints\LessThan($max, $this->data_factory);
	}

	/**
	 * Get the constraint that some value is a number
	 *
	 * @return  Constraint
	 */
	public function isNumeric() {
		return new Constraints\IsNumeric($this->data_factory);
	}

	/**
	 * Get the constraint that some string has a minimum length.
	 *
	 * @param	int	$min_length
	 * @return	Constraint
	 */
	public function hasMinLength($min_length) {
		return new Constraints\HasMinLength($min_length, $this->data_factory);
	}

	/**
	 * Get a custom constraint.
	 *
	 * If the provided value !$is_ok will either use the $error (if it is a string)
	 * or provide the value to the $error callback.
	 *
	 * @param   callable          $is_ok MUST return boolean
	 * @param   string|callable   $error
	 * @return  Constraint
	 */
	public function custom(callable $is_ok, $error) {
		return new Constraints\Custom($is_ok, $error, $this->data_factory);
	}
}
