<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

/*
 * Values work around the problem, that static functions could not be used as ordinary
 * values easily in PHP.
 *
 * A value either wraps a plain value in an underlying PHP-Representation or 
 * is a possibly curried static function that could be applied to other values.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\IValue;

class Values {
    // string to be used as the origin of an anonymus function.
    const ANONYMUS_FUNCTION_ORIGIN = "anonymus_function";

    /* Construct a plain value from a PHP value. */
    static function val($value, $origin = null) {
        return new PlainValue($value, $origin);
    }

    static function application_to(Value $val) {
        return self::fn(static function(FunctionValue $fn) use ($val) {
            return $fn->apply($val)->force();
        });
    }

    static function composition() {
        return self::fn(static function(FunctionValue $l, FunctionValue $r) {
            return $l->composeWith($r);
        });
    }

    /* Construct a static function value from a closure or the name of an ordinary
     * static function. An array of arguments to be inserted in the first arguments 
     * of the static function could be passed optionally.
     */
    static function fn($function, $arity = null, $args = array()) {
        return new FunctionValue($function, true, $args, $arity);
    }

    /* Construct a static function where the values aren't unwrapped. This could
     * be used e.g. to deal with errors.
     */
    static function fn_w($function, $args = array()) {
        return new FunctionValue($function, false, $args);
    }

    /*static function _method($arity, $object, $method_name, $args = null) {
        return new FunctionValue($arity, $method_name, $object, $args);
    }*/
    static function error($reason, $origin, $others = array()) {
        return new ErrorValue($reason, $origin, $others);
    }
}

?>
