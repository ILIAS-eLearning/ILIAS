<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Types;

/**
 */
abstract class Type {
	/**
	 * Check whether value is contained in type.
	 *
	 * @param	mixed	$value
	 * @return	bool
	 */
	abstract public function contains($value);
}