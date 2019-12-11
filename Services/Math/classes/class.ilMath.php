<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMath
 * Wrapper for mathematical operations
 * @author Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @author Michael Jansen <mjansen@databay.de>
 * $Id$
 */
class ilMath
{
    /**
     * @var ilMathAdapter
     */
    protected static $default_adapter = null;

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @param int $scale
     * @return mixed
     */
    public static function _add($left_operand, $right_operand, $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->add($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @param int $scale
     * @return mixed
     */
    public static function _div($left_operand, $right_operand, $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->div($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $operand
     * @param int|float $modulu
     * @return int
     */
    public static function _mod($operand, $modulu)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->mod($operand, $modulu);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @param int $scale
     * @return mixed
     */
    public static function _mul($left_operand, $right_operand, $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->mul($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @param int $scale
     * @return mixed
     */
    public static function _pow($left_operand, $right_operand, $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->pow($left_operand, $right_operand, $scale);
    }

    /**
     * @param int|float $operand
     * @param int $scale
     * @return mixed
     */
    public static function _sqrt($operand, $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->sqrt($operand, $scale);
    }

    /**
     * @param int|float $left_operand
     * @param int|float $right_operand
     * @param int $scale
     * @return mixed
     */
    public static function _sub($left_operand, $right_operand, $scale = 50)
    {
        $adapter = static::getDefaultAdapter();

        return $adapter->sub($left_operand, $right_operand, $scale);
    }

    public static function isCoprimeFraction($numerator, $denominator)
    {
        $gcd = self::getGreatestCommonDivisor(abs($numerator), abs($denominator));

        return $gcd == 1 ? true : false;
    }

    /**
     * @param mixed $a
     * @param mixed $b
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

    /**
     * @param ilMathAdapter $adapter
     */
    public static function setDefaultAdapter(ilMathAdapter $adapter)
    {
        static::$default_adapter = $adapter;
    }

    /**
     * @return ilMathAdapter
     */
    public static function getDefaultAdapter()
    {
        if (null === static::$default_adapter) {
            static::$default_adapter = static::getFirstValidAdapter();
        }

        return static::$default_adapter;
    }

    /**
     * @param null|string $adapter
     * @return ilMathAdapter
     * @throws ilMathException
     */
    public static function getInstance($adapter = null)
    {
        if (null === $adapter) {
            return static::getFirstValidAdapter();
        }

        $class_name    = 'ilMath' . $adapter . 'Adapter';
        $path_to_class = realpath('Services/Math/classes/class.' . $class_name . '.php');

        if (!is_file($path_to_class) || !is_readable($path_to_class)) {
            require_once 'Services/Math/exceptions/class.ilMathException.php';
            throw new ilMathException(sprintf(
                'The math adapter %s is not valid, please refer to a class implementing %s',
                $adapter,
                ilMathAdapter::class
            ));
        }

        require_once $path_to_class;
        if (!class_exists($class_name) || !is_subclass_of($class_name, ilMathAdapter::class)) {
            require_once 'Services/Math/exceptions/class.ilMathException.php';
            throw new ilMathException(sprintf(
                'The math adapter class %s is not valid, please refer to a class implementing %s',
                $class_name,
                ilMathAdapter::class
            ));
        }

        return new $class_name();
    }

    /**
     * @return ilMathAdapter
     */
    public static function getFirstValidAdapter()
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
    public static function __callStatic($method, $args)
    {
        if (strpos($method, '_') === 0) {
            $method = substr($method, 1);
        }

        $adapter = static::getDefaultAdapter();

        return call_user_func_array([$adapter, $method], $args);
    }
}
