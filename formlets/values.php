<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Values work around the problem, that functions could not be used as ordinary
 * values easily in PHP.
 *
 * A value either wraps a plain value in an underlying PHP-Representation or 
 * is a possibly curried function that could be applied to other values.
 */

require_once("formlets/checking.php");

abstract class Value {
    private $_origins; // array of strings

    public function __construct($origins) {
        guardEach($origins, "guardIsString");
        $this->_origins = $origins;
    }

    public function origins() {
        return $this->_origins;
    }

    /* Get the value in the underlying PHP-representation. 
     * Throws GetError when value represents a function.
     */
    abstract public function get();
    /* Apply the value to another value, yielding a new value.
     * Throws ApplyError when value represents a plain value.
     */
    abstract public function apply(Value $to);

    /* Check whether value could be applied to another value. */
    abstract public function isApplicable();

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

    public function __construct($value, $origins) {
        $this->_value = $value;
        parent::__construct($origins);
    }

    public function get() {
        return $this->_value;
    }

    public function apply(Value $to) {
        throw new ApplyError("PlainValue", "any Value");
    }

    public function isApplicable() {
        return false;
    }

    public function isError() {
        return false;
    }

    public function error() {
        throw new Exception("Implementation problem.");
    }
}

/* Construct a plain value from a PHP value. */
function _val($value, $origins = array()) {
    return new PlainValue($value, $origins);
}


final class FunctionValue extends Value {
    private $_arity; // int
    private $_function; // string
    private $_args; // array
    private $_reifyExceptions; // array
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
    public function __construct( $function, $args = null
                               , $reify_exceptions = null, $origins = array()) {
        if (is_string($function))
            guardIsCallable($function);
        else
            guardIsClosure($function);

        $args = defaultTo($args, array());
        $reify_exceptions = defaultTo($reify_exceptions, array());

        guardIsArray($args);
        guardIsArray($reify_exceptions);

        foreach($args as $key => $value) {
            $args[$key] = $this->toValue($value);
        }

        $refl = new ReflectionFunction($function);
        $this->_arity = $refl->getNumberOfParameters() - count($args);
        if ($this->_arity < 0) {
            throw new Exception("FunctionValue::__construct: more args then parameters.");
        }

        $this->_function = $function;
        $this->_args = $args;
        $this->_reify_exceptions = $reify_exceptions;
        
        parent::__construct($origins);
    }

    protected function withOriginalValue($value) {
        return new FunctionValue( $this->_function
                                , $this->_args
                                , $this->_reify_exceptions
                                , $this->origins()
                                );
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
            $this->_result = $this->toValue($res); 
        }
        return $this->_result; 
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
    public function apply(Value $to) {
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
                                , $this->_args
                                , $re
                                , $this->origins()
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

    /* Helper to create a new function value with one less arity. */
    private function deferredCall($args, $next_value) {
        $args[] = $next_value;
        return new FunctionValue( $this->_function
                                , $args
                                , $this->_reify_exceptions
                                , $this->origins()
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
                    return _error($e->getMessage(), $this);
                }
            }
            throw $e;
        }
    }

    /* Helper to calculate the function result without error catching. */
    private function rawActualCall() {
        $res = $this->evalArgs(); 
        $args = $res[0];
        $error = $res[1];

        if ($error) {
            return _error("Function arguments contain errors.", $this);
        }

        if ($this->_function == "explode")
            print_r(array($this->_function, $args));

        return call_user_func_array($this->_function, $args);
    }

    /* Helper to get the values of the arguments to the function. */
    private function evalArgs() {
        $res = array();
        $error = false;
        foreach ($this->_args as $value) {
            if ($value->isError()) {
                $error = true;
                $res[] = $value;
            }
            if ($value->isApplicable()) {
                $res[] = $value;
            }
            else {
                $res[] = $value->get();
            } 
        }
        return array($res, $error);
    }

    /* Turn a thing to a value if it is not already one. */
    private function toValue($val) {
        if ($val instanceof Value) {
            return $val;
        }
        else {
            return _val($val, $this->origins());
        }            
    }
}

/* Construct a function value from an arity and the name of an ordinary
 * function. Arity is the number of arguments of the function. An array
 * of arguments to be inserted in the first arguments of the function
 * could be passed 
 */
function _fn($function, $args = array()) {
    return new FunctionValue($function, $args);
}

/*function _method($arity, $object, $method_name, $args = null) {
    return new FunctionValue($arity, $method_name, $object, $args);
}*/

/* Value representing an error. */
final class ErrorValue extends Value {
    private $_reason; // string
    private $_original_value;

    public function originalValue() {
        return $this->_original_value;
    }

    public function __construct($reason, Value $original_value) {
        $this->_reason = $reason;
        $this->_original_value = $original_value;
        parent::__construct($original_value->origins());
    }

    public function get() {
        throw new GetError("ErrorValue");
    } 

    public function apply(Value $to) {
        return $this;
    }

    public function isApplicable() {
        return true;
    }

    public function isError() {
        return true;
    }

    public function error() {
        return $this->_reason;
    }
}

function _error($reason, Value $original_value) {
    return new ErrorValue($reason, $original_value);
}

?>
