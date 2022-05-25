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

/**
 * @method static _round(mixed $value, int $precision) : string
 * @method static _equals(mixed $left_operand, mixed $right_operand, int $scale = null) : bool
 */
class ilMath
{
    protected static ?ilMathAdapter $default_adapter = null;

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _add($left_operand, $right_operand, int $scale = 50)
    {
        return static::getDefaultAdapter()->add($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     * @throws ilMathDivisionByZeroException
     */
    public static function _div($left_operand, $right_operand, int $scale = 50)
    {
        return static::getDefaultAdapter()->div($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $operand
     * @param int|float $modulu
     * @throws ilMathDivisionByZeroException
     */
    public static function _mod($operand, $modulu) : int
    {
        return static::getDefaultAdapter()->mod($operand, $modulu);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _mul($left_operand, $right_operand, int $scale = 50)
    {
        return static::getDefaultAdapter()->mul($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _pow($left_operand, $right_operand, int $scale = 50)
    {
        return static::getDefaultAdapter()->pow($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $operand
     * @return mixed
     */
    public static function _sqrt($operand, int $scale = 50)
    {
        return static::getDefaultAdapter()->sqrt($operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _sub($left_operand, $right_operand, int $scale = 50)
    {
        return static::getDefaultAdapter()->sub($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $numerator
     * @param int|float $denominator
     */
    public static function isCoprimeFraction($numerator, $denominator) : bool
    {
        $gcd = self::getGreatestCommonDivisor(abs($numerator), abs($denominator));

        return $gcd == 1;
    }

    /**
     * @param int|float  $a
     * @param int|float  $b
     * @return int|float
     */
    public static function getGreatestCommonDivisor($a, $b)
    {
        if ($b > 0) {
            return self::getGreatestCommonDivisor($b, $a % $b);
        }

        return $a;
    }

    public static function setDefaultAdapter(ilMathAdapter $adapter) : void
    {
        static::$default_adapter = $adapter;
    }

    public static function getDefaultAdapter() : ilMathAdapter
    {
        if (null === static::$default_adapter) {
            static::$default_adapter = static::getFirstValidAdapter();
        }

        return static::$default_adapter;
    }

    /**
     * @throws ilMathException
     */
    public static function getInstance(string $adapter = null) : \ilMathAdapter
    {
        if (null === $adapter) {
            return static::getFirstValidAdapter();
        }

        $class_name = 'ilMath' . $adapter . 'Adapter';
        $path_to_class = realpath('Services/Math/classes/class.' . $class_name . '.php');

        if (!is_file($path_to_class) || !is_readable($path_to_class)) {
            throw new ilMathException(sprintf(
                'The math adapter %s is not valid, please refer to a class implementing %s',
                $adapter,
                ilMathAdapter::class
            ));
        }
        if (!class_exists($class_name) || !is_subclass_of($class_name, ilMathAdapter::class)) {
            throw new ilMathException(sprintf(
                'The math adapter class %s is not valid, please refer to a class implementing %s',
                $class_name,
                ilMathAdapter::class
            ));
        }

        return new $class_name();
    }

    /**
     * @throws ilMathException
     */
    public static function getFirstValidAdapter() : ilMathAdapter
    {
        if (extension_loaded('bcmath')) {
            return static::getInstance('BCMath');
        }

        return static::getInstance('Php');
    }

    /**
     * Backward compatibility: Map all static calls to an equivalent instance method of the adapter
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public static function __callStatic(string $method, $args)
    {
        if (strpos($method, '_') === 0) {
            $method = substr($method, 1);
        }

        $adapter = static::getDefaultAdapter();

        return call_user_func_array([$adapter, $method], $args);
    }
}
