<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

class FloatValue extends ScalarValue {
	/**
	 * ScalarValue constructor. Given value must resolve to true when given to is_scalar.
	 * @param $float float
	 * @internal param string $value
	 */
	public function __construct($float) {
		if(!is_float($float))
			throw new InvalidArgumentException("The value given must be a float! See php-documentation is_float().");

		parent::__construct($float);
	}

}