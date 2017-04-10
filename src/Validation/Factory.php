<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation;

/**
 * Factory for creating restrictions.
 */
class Factory {
	// COMBINATORS

	/**
	 * Get a restriction that sequentially checks the supplied restrictions.
	 *
	 * The new restriction tells the problem of the first violated restriction.
	 *
	 * @param   Restriction[]   $others
	 * @return  Restriction
	 */
	public function seq(array $others);

	/**
	 * Get a restriction that checks the supplied restrictions in parallel.
	 *
	 * The new restriction tells the problems of all violated restrictions.
	 *
	 * @param   Restriction[]   $others
	 * @return	Restriction
	 */
	public function par(array $others);

	/**
	 * Get a negated restriction.
	 *
	 * @param   Restriction   $other
	 * @return  Restriction
	 */
	public function not(Restriction $other);

	// SOME RESTRICTOINS

	/**
	 * Get a restriction for an integer.
	 *
	 * @return  Restriction
	 */
	public function isInt();

	/**
	 * Get the restriction that some value is larger than $min.
	 *
	 * @param   int   $min
	 * @return  Restriction
	 */
	public function greaterThan($min);

	/**
	 * Get the restriction that some value is smaller then $max.
	 *
	 * @param	int   $max
	 * @return  Restriction
	 */
	public function lessThan($max);

	/**
	 * Get a custom restriction.
	 *
	 * If the provided value !$is_ok will either use the $error (if it is a string)
	 * or provide the value to the $error callback.
	 *
	 * @param   callable          $is_ok MUST return boolean
	 * @param   string|callable   $error
	 * @return  Restriction
	 */
	public function custom(callable $is_ok, $error);
}
