<?php
/**************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * TypeErrors for error checking. 
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

function guardIsValue($arg) {
    if (!($arg instanceof Value)) {
        throw new TypeError("Value", typeName($arg));
    }
}

function guardIsHTML($arg) {
    if (!($arg instanceof HTML)) {
        throw new TypeError("HTML", typeName($arg));
    }
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
