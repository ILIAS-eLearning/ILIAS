<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

require_once("Services/Filter/classes/Predicates/class.ilValue.php");

/**
 * An atom in a predicate that is a value.
 */
class ilValueInt extends ilValue {
	/**
	 * Check the inserted value.
	 *
	 * @param	mixed		$value
	 * @return	str|null			Return string with error message or null
	 *								if value is ok.
	 */
	protected function value_errors($value) {
		if (!is_int($value)) {
			return "expected int";
		}
	}
}