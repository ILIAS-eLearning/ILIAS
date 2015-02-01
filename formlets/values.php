<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Values work around the problem, that functions could not be used as ordinary
 * values easily in PHP.
 *
 * A value either wraps a plain value in an underlying PHP-Representation or 
 * is a possibly curried function that could be applied to other values.
 */

require_once("formlets/checking.php");
require_once("formlets.php");

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
}

class ApplyError extends Exception {
    public function __construct($what, $other) {
        parent::__construct("Can't apply $what to $other");
    }
}

class GetError extends Exception {
    public function __construct($what) {
        parent::__construct("Can't get value from $what");
    }
}

final class PlainValue extends Value {
    private $_value; //mixed

    public function __construct($value, $origin) {
        $this->_value = $value;
        parent::__construct($origin);
    }

    public function get() {
        return $this->_value;
    }

    public function apply(IValue $to) {
        throw new ApplyError("PlainValue", "any Value");
    }

    public function catchAndReify($exc_class) {
        return null;
    }

    public function isApplicable() {
        return false;
    }

    public function force() {
        return $this;
    }

    public function isError() {
        return false;
    }

    public function error() {
        throw new Exception("Implementation problem.");
    }
}

/* Construct a plain value from a PHP value. */
function _val($value, $origin = null) {
    return new PlainValue($value, $origin);
}

// string to be used as the origin of an anonymus function.
const ANONYMUS_FUNCTION_ORIGIN = "anonymus_function";

final class FunctionValue extends Value {
    private $_arity; // int
    private $_function; // string
    private $_unwrap_args; // string
    private $_args; // array
    private $_reify_exceptions; // array
    private $_result; // maybe Value 

    public function arity() {
        return $this->_arity;
    }

    public function args() {
        return $this->_args;
    }

    /* Create a function value by at least passing it a closure or the name of
     * a function. 
     * One could optionally pass an array of arguments for the first arguments 
     * of the function to call. This is also used in construction of new function
     * values after apply.
     * When finally calling the wrapped function, Exceptions given as 
     * reify_exceptions will be caught and turned into an ErrorValue as return.
     * 
     * ATTENTION: When you pass the name of the function, FunctionValue will not
     * know about optional arguments to your function, that is, it will only be
     * satisfied when all arguments (event optional ones) are provided.
     */
    public function __construct( $function, $unwrap_args = true, $args = null
                               , $arity = null, $reify_exceptions = null
                               , $origin = null) {
        if ($origin === null) {
            if (is_string($function)) {
                $origin = $function;
            }
            else {
                $origin = ANONYMUS_FUNCTION_ORIGIN;
            }
        } 
        parent::__construct($origin);

        if (is_string($function))
            guardIsCallable($function);
        else
            guardIsClosure($function);

        guardIsBool($unwrap_args);

        $args = defaultTo($args, array());
        $reify_exceptions = defaultTo($reify_exceptions, array());
        guardIsArray($args);
        
        guardIfNotNull($arity, "guardIsUInt");
        guardIsArray($reify_exceptions);

        foreach($args as $key => $value) {
            $args[$key] = $this->toValue($value, null);
        }

        if ($arity === null) {
            $refl = new ReflectionFunction($function);
            $this->_arity = $refl->getNumberOfParameters() - count($args);
        }
        else {
            $this->_arity = $arity - count($args);
        }
        if ($this->_arity < 0) {
            throw new Exception("FunctionValue::__construct: more args then parameters.");
        }

        $this->_function = $function;
        $this->_unwrap_args = $unwrap_args;
        $this->_args = $args;
        $this->_reify_exceptions = $reify_exceptions;
    }

    /* If the function is satisfied get the result. Will only be calculated 
     * once.
     */
    public function result() {
        if (!$this->isSatisfied()) {
            throw new Exception("Problem with implementation.");
        }

        if ($this->_result === null) {
            $res = $this->actualCall();
            $this->_result = $this->toValue($res, $this->origin());
        }
        return $this->_result; 
    }

    public function force() {
        // TODO: Maybe thats enough for the moment...
        if ($this->isSatisfied()) {
            return $this->result();
        }   
        return $this;
    }

    /* Is the function applied enough times to have a result? */
    public function isSatisfied() {
        return $this->_arity === 0;
    }

    /* Get the value from the result of the function if it is
     * satisfied. Throw otherwise.
     */
    public function get() {
        if ($this->isSatisfied()) {
            return $this->result()->get();
        }
        throw new GetError("FunctionValue");
    } 

    /* Apply the function to a value, producing a new value. */
    public function apply(IValue $to) {
        if ($this->isSatisfied()) {
            return $this->result()->apply($to);
        }

        // The call should also guarantee, that $this->args
        // gets copied, so the function value could be used
        // more than once for a curried call.
        return $this->deferredCall($this->_args, $to);
    }

    /* Define a subclass of Exception to be caught and returned
     * as an ErrorValue instead of being thrown to the outside
     * of apply.
     */
    public function catchAndReify($exc_class) {
        guardIsString($exc_class);
        $re = $this->_reify_exceptions;
        $re[] = $exc_class;
        return new FunctionValue( $this->_function
                                , $this->_unwrap_args
                                , $this->_args
                                , $this->_arity + count($this->_args)
                                , $re
                                , $this->origin()
                                );
    }
    
    /* Check weather the value is applicable, that is true if the function is 
     * not satisfied and the applicability of the result otherwise. 
     */
    public function isApplicable() {
        if ($this->isSatisfied()) {
            return $this->result()->isApplicable();
        }

        return true;
    }

    /* If the function is not satisfied, it is no error, otherwise the decision
     * is dispatched to the result.
     */
    public function isError() {
        if ($this->isSatisfied()) {
            return $this->result()->isError();
        }

        return false;
    }

    /* Tries to return the error for the result if function is satisfied, throws
     * otherwise.
     */
    public function error() {
        if ($this->isSatisfied()) {
            return $this->result()->error();
        }
        throw new Exception("Implementation error.");
    }

    /* Compose this function value with another, that is, apply the other function
     * first and then apply the result to this function.
     */
    public function composeWith(FunctionValue $other) {
        return _fn_w(function($value) use ($other) {
            $res = $other->apply($value)->force();
            return $this->apply($res)->force();
        });
    }


    /* Helper to create a new function value with one less arity. */
    private function deferredCall($args, $next_value) {
        $args[] = $next_value;
        return new FunctionValue( $this->_function
                                , $this->_unwrap_args
                                , $args
                                , $this->_arity + count($this->_args)
                                , $this->_reify_exceptions
                                , $this->origin()
                                );
    }

    /* Helper to calculate the actual result of the function with error caching. */
    private function actualCall() {
        try {
            return $this->rawActualCall();
        }
        catch(Exception $e) {
            foreach ($this->_reify_exceptions as $exc_class) {
                if ($e instanceof $exc_class) {
                    return _error($e->getMessage(), $this->origin());
                }
            }
            throw $e;
        }
    }

    /* Helper to calculate the function result without error catching. */
    private function rawActualCall() {
        if ($this->_unwrap_args) {
            $args  = array();
            $errors = array();
            $this->evalArgs($args, $errors); 

            if (count($errors) > 0) {
                return _error( "Function arguments contain errors."
                             , $this->origin()
                             , $errors
                             );
            }
        }
        else {
            $args = $this->_args;
        }

        return call_user_func_array($this->_function, $args);
    }

    /* Helper to get the values of the arguments to the function. */
    private function evalArgs(&$res, &$errors) {
        foreach ($this->_args as $value) {
            if ($value->isError()) {
                $errors[] = $value->force();
                $res[] = $value;
            }
            else if ($value->isApplicable()) {
                $res[] = $value;
            }
            else {
                $res[] = $value->get();
            } 
        }
    }

    /* Turn a thing to a value if it is not already one. */
    private function toValue($val, $origin) {
        if ($val instanceof Value) {
            return $val;
        }
        else {
            return _val($val, $origin);
        }            
    }
}

function _application_to(Value $val) {
    return _fn(function(FunctionValue $fn) use ($val) {
        return $fn->apply($val)->force();
    });
}

function _composition() {
    return _fn(function(FunctionValue $l, FunctionValue $r) {
        return $l->composeWith($r);
    });
}

/* Construct a function value from a closure or the name of an ordinary
 * function. An array of arguments to be inserted in the first arguments 
 * of the function could be passed optionally.
 */
function _fn($function, $arity = null, $args = array()) {
    return new FunctionValue($function, true, $args, $arity);
}

/* Construct a function where the values aren't unwrapped. This could
 * be used e.g. to deal with errors.
 */
function _fn_w($function, $args = array()) {
    return new FunctionValue($function, false, $args);
}

/*function _method($arity, $object, $method_name, $args = null) {
    return new FunctionValue($arity, $method_name, $object, $args);
}*/

/* Value representing an error. */
final class ErrorValue extends Value {
    private $_reason; // string
    private $_others; // array of other errors
    private $_dict; // dictionary with errors or null

    public function others() {
        return $this->_others;
    }

    public function __construct($reason, $origin, $others = array()) {
        guardIsString($reason);
        guardEach($others, "guardIsErrorValue");
        $this->_reason = $reason;
        $this->_others = $others;
        $this->_dict = null;
        
        parent::__construct($origin);
    }

    public function get() {
        throw new GetError("ErrorValue");
    } 

    public function apply(IValue $to) {
        return $this;
    }

    public function catchAndReify($exc_class) {
        return $this;
    }

    public function isApplicable() {
        return true;
    }

    public function force() {
        return $this;
    }

    public function isError() {
        return true;
    }

    public function error() {
        return $this->_reason;
    }
    
    /**
     * Get a dictionary of the errors that lead to this error in the form of
     * origin => [error]. If error has more than one origin, the origins are
     * merge together to one string separated by ";".
     */
    public function toDict() {
        if ($this->_dict !== null) {
            return $this->_dict;
        }

        $_dict = array();

        // Record error for the origin of this error.
        $_dict[$this->origin()] = array($this->error());

        // Get all errors contained in others 
        array_map( function($err) use (&$_dict) {
                $d = $err->toDict();

                // Insert each origin/errors pair in our result
                // array.
                foreach($d as $o => $es) {
                    if (!isset($_dict[$o])) {
                        $_dict[$o] = array(); 
                    }
                    foreach($es as $e) {
                        $_dict[$o][] = $e;
                    }
                }    
            }
            , $this->_others
            );

        $this->_dict = $_dict;
        return $this->_dict;
    }
}


function _error($reason, $origin, $others = array()) {
    return new ErrorValue($reason, $origin, $others);
}

function defaultTo($arg, $default) {
    if ($arg === null) {
        return $default;
    }
    return $arg;
}

?>
