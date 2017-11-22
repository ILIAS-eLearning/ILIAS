<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
abstract class Type {
	/**
	 * Get a printable representation of that type.
	 *
	 * @return	string
	 */
	abstract public function repr();

	/**
	 * Check whether value is contained in type.
	 *
	 * @param	mixed	$value
	 * @return	bool
	 */
	abstract public function contains($value);

	/**
	 * Turn a flat array into a value structured according to type.
	 *
	 * ATTENTION: The array will be consumed during that process.
	 *
	 * @param	array	&$value
	 * @return	bool
	 */
	abstract public function unflatten(array &$value);

	/**
	 * Make the value flat, that is, turn nested tuples or options into
	 * a flat array (like (a,(b,c)) -> (a,b,c)) and wrap other values in
	 * array.
	 *
	 * ATTENTION: The array will be consumed during that process.
	 *
	 * @param	mixed	$value
	 * @return	array
	 */
	abstract public function flatten($value);
}
