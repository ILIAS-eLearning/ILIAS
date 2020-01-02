<?php
/**
 * Created by PhpStorm.
 * User: otruffer
 * Date: 21.04.17
 * Time: 17:26
 */

namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Value;

interface ScalarValueFactory
{

    /**
     * @param bool $bool
     *
     * @return BooleanValue
     */
    public function boolean($bool);


    /**
     * @param float $float
     *
     * @return FloatValue
     */
    public function float($float);


    /**
     * @param int $integer
     *
     * @return IntegerValue
     */
    public function integer($integer);


    /**
     * @param string $string
     *
     * @return StringValue
     */
    public function string($string);


    /**
     * Tries to wrap a Value. Stays unchanged if the given value already is a Background Task Value.
     *
     * @param $value
     *
     * @return Value
     * @throws InvalidArgumentException
     */
    public function wrapValue($value);


    /**
     * @param $scalar
     *
     * @return ScalarValue
     */
    public function scalar($scalar);
}
