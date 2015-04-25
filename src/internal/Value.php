<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets\Internal;

/*
 * Values work around the problem, that functions could not be used as ordinary
 * values easily in PHP.
 *
 * A value either wraps a plain value in an underlying PHP-Representation or 
 * is a possibly curried function that could be applied to other values.
 */

abstract class Value implements IValue {
    private $_origin; // string or null

    public function __construct($origin) {
        guardIfNotNull($origin, "guardIsString");
        $this->_origin = $origin;
    }

    /**
     * The origin of a value is the location in the 'real' world, where the
     * value originates from. It's represented by a string.
     */
    public function origin() {
        return $this->_origin;
    }

    /* Get the value in the underlying PHP-representation. 
     * Throws GetError when value represents a function.
     */
    abstract public function get();
    /* Apply the value to another value, yielding a new value.
     * Throws ApplyError when value represents a plain value.
     */
    abstract public function apply(IValue $to);

    /* Check whether value could be applied to another value. */
    abstract public function isApplicable();

    /* Returns a version of the value thats evaluated as far as 
     * possible. 
     */
    abstract public function force();

    /* Check whether this is an error value. */ 
    abstract public function isError();
    /* Get the reason for the error. */ 
    abstract public function error();

    // Helper function that defaults to $default if $arg is null
    // and returns $arg otherwise.
    protected static function defaultTo($arg, $default) {
        if ($arg === null) {
            return $default;
        }
        return $arg;
    }

}

?>
