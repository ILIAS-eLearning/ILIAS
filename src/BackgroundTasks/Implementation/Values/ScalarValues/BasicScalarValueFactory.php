<?php

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Value;

trait BasicScalarValueFactory
{

    /**
     * @param bool $bool
     *
     * @return BooleanValue
     */
    protected function boolean($bool)
    {
        $value = new BooleanValue();
        $value->setValue($bool);

        return $value;
    }


    /**
     * @param float $float
     *
     * @return FloatValue
     */
    protected function float($float)
    {
        $value = new FloatValue();
        $value->setValue($float);

        return $value;
    }


    /**
     * @param int $integer
     *
     * @return IntegerValue
     */
    protected function integer($integer)
    {
        $value = new IntegerValue();
        $value->setValue($integer);

        return $value;
    }


    /**
     * @param string $string
     *
     * @return StringValue
     */
    protected function string($string)
    {
        $value = new StringValue();
        $value->setValue($string);

        return $value;
    }


    /**
     * Tries to wrap a Value. Stays unchanged if the given value already is a Background Task Value.
     *
     * @param $value
     *
     * @return Value
     * @throws InvalidArgumentException
     */
    protected function wrapValue($value)
    {
        // It's already a Value. We don't need to wrap it.
        if ($value instanceof Value) {
            return $value;
        }

        if (is_scalar($value)) {
            return $this->wrapScalar($value);
        }

        throw new InvalidArgumentException("The given parameter is not a Background Task Value and cannot be wrapped in a Background Task Value: "
            . var_export($value, true));
    }


    /**
     * @param $scalar
     *
     * @return ScalarValue
     */
    protected function scalar($scalar)
    {
        $value = new ScalarValue();
        $value->setValue($scalar);

        return $value;
    }


    /**
     * @param $value
     *
     * @return BooleanValue|FloatValue|IntegerValue|ScalarValue|StringValue
     * @throws InvalidArgumentException
     */
    protected function wrapScalar($value)
    {
        if (is_string($value)) {
            return $this->string($value);
        }
        if (is_bool($value)) {
            return $this->boolean($value);
        }
        if (is_integer($value)) {
            return $this->integer($value);
        }
        if (is_float($value)) {
            return $this->float($value);
        }
        if (is_scalar($value)) {
            return $this->scalar($value);
        }
        throw new InvalidArgumentException("The given value " . var_export($value, true)
            . " is not a scalar and cannot be wrapped.");
    }
}