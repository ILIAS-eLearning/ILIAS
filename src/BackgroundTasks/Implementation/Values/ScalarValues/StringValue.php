<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;



use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;

class StringValue extends ScalarValue {
	/**
	 * ScalarValue constructor. Given value must resolve to true when given to is_scalar.
	 * @param $string
	 * @throws InvalidArgumentException
	 * @internal param string $value
	 */
	public function __construct($string) {
		if(!is_string($string))
			throw new InvalidArgumentException("The value given must be a string! See php-documentation is_string().");

		parent::__construct($string);
	}

}