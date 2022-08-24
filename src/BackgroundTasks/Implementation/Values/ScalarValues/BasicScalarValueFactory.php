<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Value;

trait BasicScalarValueFactory
{
    protected function boolean(bool $bool): BooleanValue
    {
        $value = new BooleanValue();
        $value->setValue($bool);

        return $value;
    }

    protected function float(float $float): FloatValue
    {
        $value = new FloatValue();
        $value->setValue($float);

        return $value;
    }

    protected function integer(int $integer): IntegerValue
    {
        $value = new IntegerValue();
        $value->setValue($integer);

        return $value;
    }

    protected function string(string $string): StringValue
    {
        $value = new StringValue();
        $value->setValue($string);

        return $value;
    }

    /**
     * Tries to wrap a Value. Stays unchanged if the given value already is a Background Task Value.
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    protected function wrapValue($value): Value
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
     * @param mixed $scalar
     */
    protected function scalar($scalar): ScalarValue
    {
        $value = new ScalarValue();
        $value->setValue($scalar);

        return $value;
    }

    /**
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    protected function wrapScalar($value): Value
    {
        if (is_string($value)) {
            return $this->string($value);
        }
        if (is_bool($value)) {
            return $this->boolean($value);
        }
        if (is_int($value)) {
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
