<?php

/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

class Checking {
    static function typeName($arg) {
        $t = getType($arg);
        if ($t == "object") {
            return get_class($arg);
        }
        return $t;
    }

    static function guardIsString($arg) {
        if (!is_string($arg)) {
            throw new TypeError("string", self::typeName($arg));
        } 
    }

    static function guardIsInt($arg) {
        if (!is_int($arg)) {
            throw new TypeError("int", self::typeName($arg));
        } 
    }

    static function guardIsUInt($arg) {
        if (!is_int($arg) || $arg < 0) {
            throw new TypeError("unsigned int", self::typeName($arg));
        }
    }

    static function guardIsBool($arg) {
        if (!is_bool($arg)) {
            throw new TypeError("bool", self::typeName($arg));
        } 
    }

    static function guardIsArray($arg) {
        if(!is_array($arg)) {
            throw new TypeError("array", self::typeName($arg));
        }
    }

    static function guardIsObject($arg) {
        if(!is_object($arg)) {
            throw new TypeError("object", self::typeName($arg));
        }
    }

    static function guardIsCallable($arg) {
        if(!is_callable($arg)) {
            throw new TypeError("callable", self::typeName($arg));
        }
    }

    static function guardHasClass($class_name, $arg) {
        if (!($arg instanceof $arg)) {
            throw new TypeError($arg, self::typeName($arg));
        }
    }

    static function guardIsClosure($arg) {
        return self::guardHasClass("Closure", $arg);
    }

    static function guardIsException($arg) {
        return self::guardHasClass("Exception", $arg);
    }

    static function guardIsValue($arg) {
        return self::guardHasClass("Value", $arg);
    }

    static function guardIsErrorValue($arg) {
        return self::guardHasClass("ErrorValue", $arg);
    }

    static function guardIsHTML($arg) {
        return self::guardHasClass("HTML", $arg);
    }

    static function guardIsHTMLTag($arg) {
        return self::guardHasClass("HTMLTag", $arg);
    }

    static function guardIsFormlet ($arg) {
        return self::guardHasClass("Formlet", $arg);
    }

    static function guardHasArity(FunctionValue $fun, $arity) {
        if ($fun->arity() != $arity) {
            throw new TypeError( "FunctionValue with arity $arity"
                               , "FunctionValue with arity ".$fun->arity()
                               );
        }    
    }

    static function guardEach($vals, $fn) {
        self::guardIsArray($vals);
        foreach ($vals as $val) {
            self::$fn($val);
        }
    }

    static function guardEachAndKeys($vals, $fn_val, $fn_key) {
        self::guardIsArray($vals);
        foreach ($vals as $key => $val) {
            self::$fn_val($val);
            self::$fn_key($key);
        }
    }

    static function guardIfNotNull($val, $fn) {
        if ($val !== null) {
            self::$fn($val);
        }
    }
}

?>
