<?php

/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This is an attempt to a PHP implementation of the idea of formlets [1].
 * General idea is to have an abstract and composable representation of forms, 
 * called Formlets, that can be transformed to a concrete Renderer and 
 * Collector. 
 * While the Renderer is responsible for creating an HTML representation of a 
 * Formlet, the Collector is responsible for collection inputs of the user from 
 * the environment.
 *
 * The PHP implementations turns out to be a little more complex, since stuff 
 * like currying and functions as values is not as handy as in functional 
 * languages.
 *
 * [1] http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf
 *     The Essence of Form Abstraction (Cooper, Lindley, wadler, Yallop)
 */

global $TEST_MODE;

if ($TEST_MODE === null) {
    $TEST_MODE = true;
}

/**************************************
 * TypeErrors for error checking. 
 */

class TypeError extends Exception {
    private $expected;
    private $found;

    public function __construct($expected, $found) {
        $this->expected = $expected;
        $this->found = $found;

        parent::__construct("Expected $expected, found $found...");
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

function guardIsName($arg) {
    guardIsString($arg);
    // ToDo: implement properly
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

function guardHasArity(FunctionValue $fun, $arity) {
    if ($fun->arity() != $arity) {
        throw new TypeError( "FunctionValue with arity $arity"
                           , "FunctionValue with arity ".$fun->arity()
                           );
    }    
}


/******************************************************************************
 * Fairly simple implementation of a Renderer. Can render strings and supports
 * combining of renderers. A more sophisticated version could be build upon
 * HTML primitives.
 */

abstract class Renderer {
    /* Returns a string. */
    abstract public function render();
}

/* Renderer that combines two sub renderers by adding the output of the 
 * renderers.
 */
class CombinedRenderer extends Renderer {
    private $l; // Renderer
    private $r; // Renderer

    public function __construct(Renderer $left, Renderer $right) {
        $this->l = $left;
        $this->r = $right;
    }

    public function render() {
        return $this->l->render().$this->r->render();
    }
}

/* A renderer that produces a constant output. */
class ConstRenderer extends Renderer {
    private $content; // string

    public function __construct($content) {
        guardIsString($content);
        $this->content = $content;
    }

    public function render() {
        return $this->content;
    }
}

/* A renderer that calls 'render' from another object to produce its output. */
class CallbackRenderer extends Renderer {
    private $call_object; // callable
    private $args; // mixed

    /* Construct with object to call and an array of arguments to be passed
     * to said óbjects render method.
     */
    public function __construct($call_object, $args) {
        guardIsObject($call_object);
        $this->call_object = $call_object;
        $this->args = $args;
    }

    public function render() {
        $res = $this->call_object->render($this->args);
        guardIsString($res);
        return $res; 
    }
}


/******************************************************************************
 * Values work around the problem, that functions could not be used as ordinary
 * values easily in PHP.
 *
 * A value either wraps a plain value in an underlying PHP-Representation or 
 * is a possibly curried function that could be applied to other values.
 */

abstract class Value {
    private $_origin; // string

    public function __construct($origin) {
        if ($origin !== null)
            guardIsString($origin);
        $this->_origin = $origin;
    }

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
    abstract public function apply(Value $to);

    /* Check weather value could be applied to another value. */
    abstract public function isApplicable();

    /* EXPERIMENTAL */
    abstract public function isError();
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
    private $value; //mixed

    public function __construct($value, $origin = null) {
        $this->value = $value;
        parent::__construct($origin);
    }

    public function get() {
        return $this->value;
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
function _plain($value, $origin = null) {
    return new PlainValue($value, $origin);
}


final class FunctionValue extends Value {
    private $_arity; // int
    private $_function_name; // string
    private $_call_object; // object
    private $_args; // array
    private $_reifyExceptions; // array
    private $_result; // maybe Value 

    public function arity() {
        return $this->_arity;
    }

    public function args() {
        return $this->_args;
    }

    /* Create a function value by at least passing it an arity, that is a number
     * of required arguments and a name of a function to be called. Optionaly an
     * object could be passed, then function_name refers to a method of that 
     * object. One could also optionally pass an array of arguments for the first
     * arguments of the function to call. This is also used in construction of
     * new function values after apply.
     * When finally calling the wrapped function, Exceptions given as 
     * reify_exceptions will be caught and turned into an ErrorValue as return.
     */
    public function __construct($arity, $function_name, $call_object = null, $args = null, $reify_exceptions = null) {
        $args = defaultTo($args, array());
        $reify_exceptions = defaultTo($reify_exceptions, array());

        foreach($args as $key => $value) {
            $args[$key] = $this->toValue($value);
        }

        guardIsInt($arity);
        guardIsString($function_name);
        if ($call_object !== null) 
            guardIsObject($call_object);
        guardIsArray($args);
        guardIsArray($reify_exceptions);

        $this->_arity = $arity;
        $this->_function_name = $function_name;
        $this->_call_object = $call_object; 
        $this->_args = $args;
        $this->_reify_exceptions = $reify_exceptions;
        
        parent::__construct(null);
    }

    public function result() {
        if ($this->_arity !== 0) {
            throw new Exception("Problem with implementation.");
        }

        if ($this->_result === null) {
            $res = $this->actualCall();
            $this->_result = $this->toValue($res); 
        }
        return $this->_result; 
    }

    public function isSatisfied() {
        return $this->_arity === 0;
    }

    public function get() {
        if ($this->isSatisfied()) {
            return $this->result()->get();
        }
        throw new GetError("FunctionValue");
    } 

    public function apply(Value $to) {
        if ($this->isSatisfied()) {
            return $this->result()->apply($to);
        }

        // EXPERIMENTAL
        if($to->isError()) {
            return $to;
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
        return new FunctionValue( $this->_arity
                                , $this->_function_name
                                , $this->_call_object
                                , $this->_args
                                , $re
                                );
    }
    
    public function isApplicable() {
        if ($this->isSatisfied()) {
            return $this->result()->isApplicable();
        }

        return true;
    }

    public function isError() {
        if ($this->isSatisfied()) {
            return $this->result()->isError();
        }

        return false;
    }

    public function error() {
        if ($this->isSatisfied()) {
            return $this->result()->error();
        }
        throw new Exception("Implementation error.");
    }

    private function deferredCall($args, $next_value) {
        $args[] = $next_value;
        return new FunctionValue( $this->_arity - 1
                                , $this->_function_name
                                , $this->_call_object
                                , $args
                                , $this->_reify_exceptions
                                );
    }

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

    private function rawActualCall() {
        $args = $this->evalArgs(); 
        if ($this->_call_object === null) {
            return call_user_func_array
                    ($this->_function_name, $args);
        }
        else {
            return call_user_func_array
                    ( array($this->_call_object, $this->_function_name)
                    , $args);
        }
    }

    private function evalArgs() {
        $res = array();
        foreach ($this->_args as $value) {
            if ($value->isApplicable()) {
                $res[] = $value;
            }
            else {
                $res[] = $value->get();
            } 
        }
        return $res;
    }

    private function toValue($val) {
        if ($val instanceof Value) {
            return $val;
        }
        else {
            return _plain($val);
        }            
    }
}

/* Construct a function value from an arity and the name of an ordinary
 * function. Arity is the number of arguments of the function. An array
 * of arguments to be inserted in the first arguments of the function
 * could be passed 
 */
function _function($arity, $function_name, $args = null) {
    return new FunctionValue($arity, $function_name, null, $args);
}

function _method($arity, $object, $function_name, $args = null) {
    return new FunctionValue($arity, $function_name, $object, $args);
}


// EXPERIMENTAL
final class ErrorValue extends Value {
    private $_reason; // string
    private $_original_value; // Value

    public function error() {
        return $this->_reason;
    }

    public function originalValue() {
        return $this->_original_value;
    }

    public function __construct($reason, Value $original_value) {
        $this->_reason = $reason;
        $this->_original_value = $original_value;
        // ToDo: don't know wether this is clever.
        parent::__construct($original_value->origin());
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
}

function _error($reason, Value $original_value) {
    return new ErrorValue($reason, $original_value);
}

/* Turn a value to two dictionaries:
 *  - one contains the origins and the original values
 *  - one contains the origins and the errors on those values.
 */
class toOriginDicts {
    static public function computeFrom(Value $value) {
        $values = array();
        $errors = array();
        self::dispatchValue($value, $values, $errors);
        return array($values, $errors);
    }

    public static function dispatchValue($value, &$values, &$errors) {
        if ($value instanceof ErrorValue) {
            self::handleError($value, $values, $errors); 
        } 
        elseif ($value instanceof FunctionValue) {
            self::handleFunction($value, $values, $errors);
        }
        else {
            self::handleValue($value, $values, $errors); 
        }
    }

    public static function handleError($value, &$values, &$errors) {
        $origin = $value->origin();
        if ($origin !== null) {
            if (!array_key_exists($origin, $errors)) {
                $errors[$origin] = array();
            }
            $errors[$origin][] = $value->error();
        }
        self::dispatchValue($value->originalValue(), $values, $errors);
    }

    public static function handleFunction($value, &$values, &$errors) {
        foreach($value->args() as $value) {
            self::dispatchValue($value, $values, $errors);
        }
    }

    public static function handleValue($value, &$values, &$errors) {
        $origin = $value->origin();
        if ($origin !== null) {
            $values[$origin] = $value->get();
        }
    }
}


/******************************************************************************
 * Base class and primitives for collectors.
 */

abstract class Collector {
    /* Expects an array. Tries to collect it's desired input from it and returns
     * it as a Value. Throws if desired content can not be found. A missing 
     * input is not to be considered as a regular error but rather points at 
     * some problem in the implementation or tampering with the input, thus we 
     * throw.
     */
    abstract public function collect($env);
    /* Check weather Collector collects something. */
    abstract public function isNullaryCollector();
}

class MissingInputError extends Exception {
    private $name; //string
    public function __construct($name) {
        $this->name = $name;
        parent::__construct("Missing input $name.");
    }
}

/* A collector that collects nothing and will be dropped by apply collectors. */
final class NullaryCollector extends Collector {
    public function collect($env) {
        die("NullaryCollector::collect: This should never be called.");
    }
    public function isNullaryCollector() {
        return true;
    }
}

/* A collector that always returns a constant value. */
final class ConstCollector extends Collector {
    private $value; // Value

    public function __construct(Value $value) {
        $this->value = $value;
    }

    public function collect($env) {
        return $this->value;
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that applies the input from its left collector to the input
 * from its right collector.
 */
final class ApplyCollector extends Collector {
    private $l;
    private $r;

    public function __construct(Collector $left, Collector $right) {
        $this->l = $left;
        $this->r = $right;
    }

    public function collect($env) {
        $l = $this->l->collect($env);
        $r = $this->r->collect($env);
        return $l->apply($r);
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that does a predicate check on the input from another
 * collector and return an error if the predicate fails.
 */
final class CheckedCollector extends Collector {
    private $_collector; // Collector
    private $_predicate; // FunctionValue
    private $_error; // string

    public function __construct(Collector $collector, FunctionValue $predicate, $error) {
        guardIsString($error);
        guardHasArity($predicate, 1);
        $this->_collector = $collector;
        $this->_predicate = $predicate;
        $this->_error = $error;
    }

    public function collect($env) {
        $res = $this->_collector->collect($env);
        if ($res->isError()) {
            return $res;
        }

        // TODO: Maybe check for PlainValue on result before
        // doing this?
        if ($this->_predicate->apply($res)->get()) {
            return $res;
        }
        else {
            return _error($this->_error, $res);
        }
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that collects a string from input. */
final class StringCollector extends Collector {
    private $_name; // string

    public function __construct($name) {
        guardIsName($name);
        $this->_name = $name;
    }

    public function collect($env) {
        if (!array_key_exists($this->_name, $env)) {
            throw new MissingInputError($this->_name);
        }
        guardIsString($env[$this->_name]);
        return _plain($env[$this->_name], $this->_name);
    }

    public function isNullaryCollector() {
        return false;
    }
}

/***********/
/* Helpers */
/***********/

function andR($carry, $item) {
    return $carry && $item;
}

function _and($arr) {
    return array_reduce($arr, "andR", true);
}

function _o_f($val) {
    return $val?"OK":"FAIL"; 
}

function defaultTo($arg, $default) {
    if ($arg === null) {
        return $default;
    }
    return $arg;
}

/******************************************************************************
 * The NameSource is used to create unique names for every input. This is 
 * needed for composability of the Formlets without the need to worry about
 * names.
 * It should only be instantiated once per process. Unsafe should only be used
 * for testing or debugging.
 */

final class NameSource {
    private $i;
    private $used = false;
    static private $instantiated = false;
    
    public static function instantiate() {
        if (static::$instantiated) {
            throw new Exception("NameSource can only be instantiated once.");
        }
        return new NameSource(0);
    } 

    public static function unsafeInstantiate() {
        return new NameSource(0);
    }

    private function __construct($i) {
        $this->i = $i;
    }

    public function getNameAndNext() {
        if ($this->used) {
            throw new Exception("NameSource can only be used once.");
        }

        $this->used = true;
        return array
            ( "name" => "input".$this->i
            , "name_source" => new NameSource($this->i + 1)
            );
    }
}

/************/
/* Formlets */
/************/

abstract class FormletFactory {
    abstract static function instantiate($args);
}

abstract class Formlet {
    public abstract function build(NameSource $name_source);
    
    /* Combine this formlet with another formlet. Yields a new formlet. */
    final public function cmb(Formlet $other) {
        return new CombinedFormlets($this, $other);
    }

    /* Get a new formlet with an additional check of a predicate on the input
     * to the formlet and an error message for the case the predicate fails.
     */
    final public function satisfies(FunctionValue $predicate, $error) {
        return new CheckedFormlet($this, $predicate, $error);
    }
}


/****************************/
/* Tests on implementations */
/****************************/

function alwaysTrue($val) {
    return true;
}

function verboseCheck_isFormlet($name, $args) {
    $name .= "Factory";
    $formlet = $name::instantiate($args);
    //$formlet_pred = $formlet
    //    ->satisfies(_function(1, alwaysTrue), "This is impossible");
    $res = $formlet->build(NameSource::unsafeInstantiate());
    $renderer_res = $res["renderer"]->render();
    return array
        ( "Formlet has correct class."
            => $formlet instanceof Formlet
        , "Renderer has correct instance"
            => $res["renderer"] instanceof Renderer
        , "Renderer returns string."
            => is_string($renderer_res)
        , "Collector has correct instance"
            => $res["collector"] instanceof Collector
        , "Name source has correct instance."
            => $res["name_source"] instanceof NameSource
        );
}

function check_isFormlet($name, $args) {
    $res = verboseCheck_isFormlet($name, $args);
    return _and($res);
}

function print_check_isFormlet($name, $args) {
    $res = verboseCheck_isFormlet($name, $args);
    echo "Checking $name:\n";
    foreach($res as $test => $result) {
        echo "\t$test: "._o_f($result)."\n";
    }
    echo "=> "._o_f(_and($res))."\n";
}


/*************/
/* PureFormlet */ 
/*************/

class PureFormletFactory extends FormletFactory {
    public static function instantiate($args) {
        guardIsValue($args[0]);
        return new PureFormlet($args[0]); 
    }
}

class PureFormlet extends Formlet {
    private $value; // mixes

    public function __construct(Value $value) {
        $this->value = $value;
    }

    public function build(NameSource $name_source) {
        return array
            ( "renderer"    => new ConstRenderer("")
            , "collector"   => new ConstCollector($this->value)
            , "name_source" => $name_source
            );
    }
}

function _pure(Value $value) {
    return new PureFormlet($value); 
}

if ($TEST_MODE) {
    print_check_isFormlet("PureFormlet", array(new PlainValue(42)));
    echo "\n";
}

/*********************/
/* CombinatedFormets */
/*********************/

class CombinedFormletsFactory extends FormletFactory {
    public static function instantiate($args) {
        return new CombinedFormlets($args[0], $args[1]);
    }
}

class CombinedFormlets extends Formlet {
    private $l; // Formlet
    private $r; // Formlet

    public function __construct(Formlet $left, Formlet $right) {
        $this->l = $left;
        $this->r = $right;
    }

    public function build(NameSource $name_source) {
        $l = $this->l->build($name_source);
        $r = $this->r->build($l["name_source"]);
        $l_empty = $l["collector"]->isNullaryCollector();
        $r_empty = $r["collector"]->isNullaryCollector();
        if ($l_empty && $r_empty) 
            $collector = new NullaryCollector();
        elseif ($r_empty)
            $collector = $l["collector"];
        elseif ($l_empty)
            $collector = $r["collector"];
        else
            $collector = new ApplyCollector($l["collector"], $r["collector"]);
        return array
            ( "renderer"    => new CombinedRenderer($l["renderer"], $r["renderer"])
            , "collector"   => $collector
            , "name_source" => $r["name_source"]
            );
    }
}

if ($TEST_MODE) {
    $pv = PureFormletFactory::instantiate(array(new PlainValue(1337)));
    print_check_isFormlet("CombinedFormlets", array($pv, $pv));
    echo "\n";
}

/******************/
/* CheckedFormlet */
/******************/

class CheckedFormletFactory extends FormletFactory {
    public static function instantiate($args) {
        return new CheckedFormlet($args[0], $args[1], $args[2]);
    } 
}

class CheckedFormlet extends Formlet {
    private $_formlet; // Formlet
    private $_predicate; // Predicate
    private $_error; // string
    
    public function __construct(Formlet $formlet, FunctionValue $predicate, $error) {
        guardIsString($error); 
        guardHasArity($predicate, 1);
        $this->_formlet = $formlet;
        $this->_predicate = $predicate;
        $this->_error = $error;
    }

    public function build(NameSource $name_source) {
        $fmlt = $this->_formlet->build($name_source);
        return array( "renderer"    => $fmlt["renderer"]
                    , "collector"   => new CheckedCollector( $fmlt["collector"]
                                                           , $this->_predicate
                                                           , $this->_error
                                                           )
                    , "name_source" => $fmlt["name_source"]
                    );
    }
}

if ($TEST_MODE) {
    print_check_isFormlet("CheckedFormlet", array
                            ( _pure(_plain(3))
                            , _function(1, "alwaysTrue")
                            , "ERROR"
                            ));
    echo "\n";
}

/*****************/
/* StaticFormlet */
/*****************/

class StaticFormletFactory extends FormletFactory {
    public static function instantiate($args) {
        return new StaticFormlet($args[0]);
    } 
}

class StaticFormlet extends Formlet {
    private $content; // string

    public function __construct($content) { 
        guardIsString($content);
        $this->content = $content;
    }

    public function build(NameSource $name_source) {
        return array
            ( "renderer"    => new ConstRenderer($this->content)
            , "collector"   => new NullaryCollector()
            , "name_source" => $name_source
            );
    }
}

function _static($content) {
    return new StaticFormlet($content);
}

if ($TEST_MODE) {
    print_check_isFormlet("StaticFormlet", array("Static"));
    echo "\n";
}

/*************/
/* TextInput */
/*************/

class TextInputFactory extends FormletFactory {
    public static function instantiate($args) {
        return new TextInput();
    } 
}

class TextInput extends Formlet {
    public function __construct() {
        
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "renderer"    => new CallbackRenderer($this, array
                                        ( "name" => $res["name"]
                                        )) 
            , "collector"   => new StringCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function render($args) {
        // TODO: this would need some more parameters 
        return "<input type='text' name='".$args["name"]."'/>";
    }
}

function _text_input() {
    return new TextInput();
}

if ($TEST_MODE) {
    print_check_isFormlet("TextInput", array("Hello World!"));
    echo "\n";
}

?>
