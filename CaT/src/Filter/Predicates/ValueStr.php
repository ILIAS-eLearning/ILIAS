<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * An atom in a predicate that is a value.
 */
class ValueStr extends Value {
	/**
	 * Check the inserted value.
	 *
	 * @param	mixed		$value
	 * @return	str|null			Return string with error message or null
	 *								if value is ok.
	 */
	protected function value_errors($value) {
		if (!is_string($value)) {
			return "expected str";
		}
	}
}