<?php

/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

/**
 * TypeErrors are needed to find typing problems that aren't revealed by PHPs 
 * type hinting. 
 */
class TypeError extends Exception {
    private $_expected;
    private $_found;

    public function __construct($expected, $found) {
        $this->_expected = $expected;
        $this->_found = $found;

        parent::__construct("Expected $expected, found $found.");
    }
}

function typeName($arg) {
    $t = getType($arg);
    if ($t == "object") {
        return get_class($arg);
    }
    return $t;
}


function guardIsString($arg) {
    if (!is_string($arg)) {
        throw new TypeError("string", typeName($arg));
    } 
}

function guardIsInt($arg) {
    if (!is_int($arg)) {
        throw new TypeError("int", typeName($arg));
    } 
}

function guardIsUInt($arg) {
    if (!is_int($arg) || $arg < 0) {
        throw new TypeError("unsigned int", typeName($arg));
    }
}

function guardIsBool($arg) {
    if (!is_bool($arg)) {
        throw new TypeError("bool", typeName($arg));
    } 
}

function guardIsArray($arg) {
    if(!is_array($arg)) {
        throw new TypeError("array", typeName($arg));
    }
}

function guardIsObject($arg) {
    if(!is_object($arg)) {
        throw new TypeError("object", typeName($arg));
    }
}

function guardIsCallable($arg) {
    if(!is_callable($arg)) {
        throw new TypeError("callable", typeName($arg));
    }
}

function guardHasClass($class_name, $arg) {
    if (!($arg instanceof $arg)) {
        throw new TypeError($arg, typeName($arg));
    }
}

function guardIsClosure($arg) {
    return guardHasClass("Closure", $arg);
}

function guardIsException($arg) {
    return guardHasClass("Exception", $arg);
}

function guardIsValue($arg) {
    return guardHasClass("Value", $arg);
}

function guardIsErrorValue($arg) {
    return guardHasClass("ErrorValue", $arg);
}

function guardIsHTML($arg) {
    return guardHasClass("HTML", $arg);
}

function guardIsHTMLTag($arg) {
    return guardHasClass("HTMLTag", $arg);
}

function guardIsFormlet ($arg) {
    return guardHasClass("Formlet", $arg);
}

function guardHasArity(FunctionValue $fun, $arity) {
    if ($fun->arity() != $arity) {
        throw new TypeError( "FunctionValue with arity $arity"
                           , "FunctionValue with arity ".$fun->arity()
                           );
    }    
}

function guardEach($vals, $fn) {
    guardIsArray($vals);
    foreach ($vals as $val) {
        call_user_func($fn, $val);
    }
}

function guardEachAndKeys($vals, $fn_val, $fn_key) {
    guardIsArray($vals);
    foreach ($vals as $key => $val) {
        call_user_func($fn_val, $val);
        call_user_func($fn_key, $key);
    }
}

function guardIfNotNull($val, $fn) {
    if ($val !== null) {
        call_user_func($fn, $val);
    }
}

?>
