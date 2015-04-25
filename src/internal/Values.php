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

class Values {
    /* Construct a plain value from a PHP value. */
    static function _val($value, $origin = null) {
        return new PlainValue($value, $origin);
    }

    static function _application_to(Value $val) {
        return _fn(static function(FunctionValue $fn) use ($val) {
            return $fn->apply($val)->force();
        });
    }

    static function _composition() {
        return _fn(static function(FunctionValue $l, FunctionValue $r) {
            return $l->composeWith($r);
        });
    }

    /* Construct a static function value from a closure or the name of an ordinary
     * static function. An array of arguments to be inserted in the first arguments 
     * of the static function could be passed optionally.
     */
    static function _fn($static function, $arity = null, $args = array()) {
        return new FunctionValue($static function, true, $args, $arity);
    }

    /* Construct a static function where the values aren't unwrapped. This could
     * be used e.g. to deal with errors.
     */
    static function _fn_w($static function, $args = array()) {
        return new FunctionValue($static function, false, $args);
    }

    /*static function _method($arity, $object, $method_name, $args = null) {
        return new FunctionValue($arity, $method_name, $object, $args);
    }*/
    static function _error($reason, $origin, $others = array()) {
        return new ErrorValue($reason, $origin, $others);
    }
}

?>
