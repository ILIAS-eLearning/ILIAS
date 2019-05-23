<?php

namespace SimpleBus\Message\CallableResolver\Exception;

class CouldNotResolveCallable extends \LogicException
{
    public static function createFor($value)
    {
        return new self(
            sprintf(
                '%s could not be resolved to a valid callable',
                static::printValue($value)
            )
        );
    }

    private static function printValue($value)
    {
        return str_replace('  ', '', str_replace("\n", '', print_r(self::convertValue($value), true)));
    }

    private static function convertValue($value)
    {
        if (is_array($value)) {
            return array_map(function ($value) {
                return self::convertObject($value);
            }, $value);
        }

        return self::convertObject($value);
    }

    private static function convertObject($value)
    {
        if (is_object($value)) {
            return get_class($value);
        }

        return $value;
    }
}
