<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

class BooleanValue extends ScalarValue {
	/**
	 * ScalarValue constructor. Given value must resolve to true when given to is_scalar.
	 * @param $boolean boolean
	 * @internal param string $value
	 */
	public function __construct($boolean) {
		if(!is_bool($boolean))
			throw new InvalidArgumentException("The value given must be a boolean! See php-documentation is_bool().");

		parent::__construct($boolean);
	}

}