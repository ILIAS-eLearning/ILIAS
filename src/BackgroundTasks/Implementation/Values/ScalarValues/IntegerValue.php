<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;


use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;

class IntegerValue extends ScalarValue {
	/**
	 * ScalarValue constructor. Given value must resolve to true when given to is_scalar.
	 * @param $integer
	 * @throws InvalidArgumentException
	 * @internal param string $value
	 */
	public function __construct($integer) {
		if(!is_integer($integer))
			throw new InvalidArgumentException("The value given must be an integer! See php-documentation is_integer().");

		parent::__construct($integer);
	}

}