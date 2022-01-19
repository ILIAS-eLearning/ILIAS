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
interface ScalarValueFactory
{
    
    /**
     * @return BooleanValue
     */
    public function boolean(bool $bool);
    
    /**
     * @return FloatValue
     */
    public function float(float $float);
    
    /**
     * @return IntegerValue
     */
    public function integer(int $integer);
    
    /**
     * @return StringValue
     */
    public function string(string $string);
    
    /**
     * Tries to wrap a Value. Stays unchanged if the given value already is a Background Task Value.
     * @param $value
     * @return Value
     * @throws InvalidArgumentException
     */
    public function wrapValue($value);
    
    /**
     * @param $scalar
     * @return ScalarValue
     */
    public function scalar($scalar);
}
