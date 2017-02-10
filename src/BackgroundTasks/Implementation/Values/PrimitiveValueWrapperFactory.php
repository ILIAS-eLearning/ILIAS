<?php

namespace ILIAS\BackgroundTasks\Implementation\Values;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\BooleanValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\FloatValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\IntegerValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\ScalarValue;
use ILIAS\BackgroundTasks\Implementation\Values\ScalarValues\StringValue;
use ILIAS\BackgroundTasks\Value;

/**
 * Class PrimitiveValueWrapperFactory
 *
 * For ease of use we want the user to be able to give some values without having to add the wrapper themselfs. This method will wrap known types in the given wrapper classes.
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class PrimitiveValueWrapperFactory {

	protected static $instance;

	protected function __construct() {
	}

	/**
	 * @return PrimitiveValueWrapperFactory
	 */
	public static function getInstance() {
		if(!static::$instance) {
			static::$instance = new PrimitiveValueWrapperFactory();
		}
		return static::$instance;
	}

	/**
	 * Tries to wrap a Value. Stays unchanged if the given value already is a Background Task Value.
	 *
	 * @param $value
	 * @return BooleanValue|FloatValue|IntegerValue|ScalarValue
	 * @throws InvalidArgumentException
	 */
	public function wrapValue($value) {
		// It's already a Value. We don't need to wrap it.
		if($value instanceof Value)
			return $value;

		if(is_scalar($value)) {
			return $this->wrapScalar($value);
		}

		throw new InvalidArgumentException("The given parameter $value is not a Background Task Value and cannot be wrapped in a Background Task Value.");
	}

	public function wrapScalar($value) {
		if(is_string($value))
			return new StringValue($value);
		if(is_bool($value))
			return new BooleanValue($value);
		if(is_integer($value))
			return new IntegerValue($value);
		if(is_float($value))
			return new FloatValue($value);

		return new ScalarValue($value);
	}
}