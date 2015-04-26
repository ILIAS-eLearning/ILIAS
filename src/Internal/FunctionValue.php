<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets\Internal;

use Exception;
use ReflectionFunction;
use Lechimp\Formlets\IValue;
use Lechimp\Formlets\Internal\Checking as C;
use Lechimp\Formlets\Internal\Values as V;

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
                $origin = V::ANONYMUS_FUNCTION_ORIGIN;
            }
        } 
        parent::__construct($origin);

        if (is_string($function))
            C::guardIsCallable($function);
        else
            C::guardIsClosure($function);

        C::guardIsBool($unwrap_args);

        $args = self::defaultTo($args, array());
        $reify_exceptions = self::defaultTo($reify_exceptions, array());
        C::guardIsArray($args);
        
        C::guardIfNotNull($arity, "guardIsUInt");
        C::guardIsArray($reify_exceptions);

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
        C::guardIsString($exc_class);
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
        return V::fn_w(function($value) use ($other) {
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
                    return V::error($e->getMessage(), $this->origin());
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
            return V::val($val, $origin);
        }            
    }
}

?>
