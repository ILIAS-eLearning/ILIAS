<?php
/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilMath
{
    /**
     * @var ilMathAdapter
     */
    protected static $default_adapter = null;

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _add($left_operand, $right_operand, int $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->add($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     * @throws ilMathDivisionByZeroException
     */
    public static function _div($left_operand, $right_operand, int $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->div($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $operand
     * @param int|float $modulu
     * @throws ilMathDivisionByZeroException
     */
    public static function _mod($operand, $modulu) : int
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->mod($operand, $modulu);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _mul($left_operand, $right_operand, int $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->mul($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _pow($left_operand, $right_operand, int $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->pow($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $operand
     * @return mixed
     */
    public static function _sqrt($operand, int $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->sqrt($operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @return mixed
     */
    public static function _sub($left_operand, $right_operand, int $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->sub($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $numerator
     * @param int|float $denominator
     */
    public static function isCoprimeFraction($numerator, $denominator) : bool
    {
        $gcd = self::getGreatestCommonDivisor(abs($numerator), abs($denominator));

        return $gcd == 1 ? true : false;
    }

    /**
     * @param int|float  $a
     * @param int|float  $b
     * @return mixed
     */
    public static function getGreatestCommonDivisor($a, $b)
    {
        if ($b > 0) {
            return self::getGreatestCommonDivisor($b, $a % $b);
        } else {
            return $a;
        }
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
