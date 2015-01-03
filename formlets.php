<?php

/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This is an attempt to a PHP implementation of the idea of formlets [1].
 * General idea is to have an abstract and composable representation of forms, 
 * called Formlets, that can be transformed to a concrete Renderer and 
 * Collector. 
 * While the Renderer is responsible for creating an HTML representation of a 
 * Formlet, the Collector is responsible for collecting inputs of the user.
 *
 * The PHP implementations turns out to be a little more complex, since stuff 
 * like currying and functions as values is not as handy as in functional 
 * languages.
 *
 * [1] http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf
 *     The Essence of Form Abstraction (Cooper, Lindley, wadler, Yallop)
 */

/**************************************
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

    public function __construct($value, $origin = null) {
        $this->_value = $value;
        parent::__construct($origin);
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
function _value($value, $origin = null) {
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
        return new FunctionValue( $this->_arity
                                , $this->_function_name
                                , $this->_call_object
                                , $this->_args
                                , $re
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
        return new FunctionValue( $this->_arity - 1
                                , $this->_function_name
                                , $this->_call_object
                                , $args
                                , $this->_reify_exceptions
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
            return _value($val);
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

function _method($arity, $object, $method_name, $args = null) {
    return new FunctionValue($arity, $method_name, $object, $args);
}

/* Value representing an error. */
final class ErrorValue extends Value {
    private $_reason; // string
    private $_original_value; // Value

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

    public function error() {
        return $this->_reason;
    }
}

function _error($reason, Value $original_value) {
    return new ErrorValue($reason, $original_value);
}

/* Turn a value to two dictionaries:
 *  - one contains the origins and the original values
 *  - one contains the origins and the errors on those values.
 */
class RenderDict {
    private $_values; // array
    private $_errors; // array

    public function value($name) {
        if (array_key_exists($name, $this->_values))
            return $this->_values[$name];
        return null;
    }

    public function errors($name) {
        if (array_key_exists($name, $this->_errors))
            return $this->_errors[$name];
        return null;
    }

    public function __construct(Value $value) {
        $res = self::computeFrom($value);
        $this->_values = $res[0]; 
        $this->_errors = $res[1]; 
    }

    private static $_emptyInst = null;

    public static function _empty() {
        // ToDo: Why does this not work?
        /*if (self::_emptyInst === null) {
            self::_emptyInst = new RenderDict(_value(0));
        }
        return self::_emptyInst;*/
        return new RenderDict(_value(0));
    }  

    public static function computeFrom(Value $value) {
        $values = array();
        $errors = array();
        self::dispatchValue($value, $values, $errors);
        return array($values, $errors);
    }

    protected static function dispatchValue($value, &$values, &$errors) {
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

    protected static function handleError($value, &$values, &$errors) {
        $origin = $value->origin();
        if ($origin !== null) {
            if (!array_key_exists($origin, $errors)) {
                $errors[$origin] = array();
            }
            $errors[$origin][] = $value->error();
        }
        self::dispatchValue($value->originalValue(), $values, $errors);
    }

    protected static function handleFunction($value, &$values, &$errors) {
        foreach($value->args() as $value) {
            self::dispatchValue($value, $values, $errors);
        }
    }

    protected static function handleValue($value, &$values, &$errors) {
        $origin = $value->origin();
        if ($origin !== null) {
            $values[$origin] = $value->get();
        }
    }
}


/******************************************************************************
 * Fairly simple implementation of a Renderer. Can render strings and supports
 * combining of renderers. A more sophisticated version could be build upon
 * HTML primitives.
 */

abstract class Renderer {
    /* Returns a string. */
    abstract public function renderValues(RenderDict $dict);
    public function render() {
        return $this->renderValues(RenderDict::_empty());
    }
}

/* Renderer that combines two sub renderers by adding the output of the 
 * renderers.
 */
class CombinedRenderer extends Renderer {
    private $_l; // Renderer
    private $_r; // Renderer

    public function __construct(Renderer $left, Renderer $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function renderValues(RenderDict $dict) {
        return $this->_l->renderValues($dict).$this->_r->renderValues($dict);
    }
}

/* A renderer that produces a constant output. */
class ConstRenderer extends Renderer {
    private $_content; // string

    public function __construct($content) {
        guardIsString($content);
        $this->_content = $content;
    }

    public function renderValues(RenderDict $dict) {
        return $this->_content;
    }
}

/* A renderer that calls 'render' from another object to produce its output. */
class CallbackRenderer extends Renderer {
    private $_call_object; // callable
    private $_args; // mixed

    /* Construct with object to call and an array of arguments to be passed
     * to said óbjects render method.
     */
    public function __construct($call_object, $args) {
        guardIsObject($call_object);
        $this->_call_object = $call_object;
        $this->_args = $args;
    }

    public function renderValues(RenderDict $dict) {
        $res = $this->_call_object->renderValues($dict, $this->_args);
        guardIsString($res);
        return $res; 
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
    abstract public function collect($inp);
    /* Check whether Collector collects something. */
    abstract public function isNullaryCollector();
}

class MissingInputError extends Exception {
    private $_name; //string
    public function __construct($name) {
        $this->_name = $name;
        parent::__construct("Missing input $name.");
    }
}

/* A collector that collects nothing and will be dropped by apply collectors. */
final class NullaryCollector extends Collector {
    public function collect($inp) {
        die("NullaryCollector::collect: This should never be called.");
    }
    public function isNullaryCollector() {
        return true;
    }
}

/* A collector that always returns a constant value. */
final class ConstCollector extends Collector {
    private $_value; // Value

    public function __construct(Value $value) {
        $this->_value = $value;
    }

    public function collect($inp) {
        return $this->_value;
    }

    public function isNullaryCollector() {
        return false;
    }
}

/* A collector that applies the input from its left collector to the input
 * from its right collector.
 */
final class ApplyCollector extends Collector {
    private $_l;
    private $_r;

    public function __construct(Collector $left, Collector $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function collect($inp) {
        $l = $this->_l->collect($inp);
        $r = $this->_r->collect($inp);
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

    public function collect($inp) {
        $res = $this->_collector->collect($inp);
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

/* A collector where the input is mapped by a function */
final class MappedCollector extends Collector {
    private $_collector; // Collector
    private $_function; // FunctionValue
    
    public function __construct(Collector $collector, FunctionValue $function) {
        guardHasArity($function, 1);
        if ($collector->isNullaryCollector()) {
            throw new TypeError("non nullary collector", typeName($collector));
        }
        $this->_collector = $collector;
        $this->_function = $function;
    }

    public function collect($inp) {
        $res = $this->_collector->collect($inp);
        if ($res->isError()) {
            return $res;
        }

        $res2 = $this->_function->apply($res);
        if (!$res2->isError() && !$res2->isApplicable()) {
            // rewrap ordinary values to keep origin.
            $res2 = _value($res2->get(), $res->origin());
        }
        return $res2;
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

    public function collect($inp) {
        if (!array_key_exists($this->_name, $inp)) {
            throw new MissingInputError($this->_name);
        }
        guardIsString($inp[$this->_name]);
        return _value($inp[$this->_name], $this->_name);
    }

    public function isNullaryCollector() {
        return false;
    }
}

function combineCollectors(Collector $l, Collector $r) {
    $l_empty = $l->isNullaryCollector();
    $r_empty = $r->isNullaryCollector();
    if ($l_empty && $r_empty) 
        return new NullaryCollector();
    elseif ($r_empty)
        return $l;
    elseif ($l_empty)
        return $r;
    else
        return new ApplyCollector($l, $r);
}


/***********/
/* Helpers */
/***********/

function defaultTo($arg, $default) {
    if ($arg === null) {
        return $default;
    }
    return $arg;
}

function keysAndValuesToHTMLAttributes($attributes) {
    $strs = "";
    foreach ($attributes as $key => $value) {
        guardIsString($key);
        if ($value !== null)
            guardIsString($value);
        $str .= " ".$key.($value !== null ? "=\"$value\"" : "");
    } 
    return $strs;
}

/******************************************************************************
 * The NameSource is used to create unique names for every input. This is 
 * needed for composability of the Formlets without the need to worry about
 * names.
 * It should only be instantiated once per process. Unsafe should only be used
 * for testing or debugging.
 */

final class NameSource {
    private $_i;
    private $_used = false;
    static private $_instantiated = false;
    
    public static function instantiate() {
        if (static::$_instantiated) {
            throw new Exception("NameSource can only be instantiated once.");
        }
        return new NameSource(0);
    } 

    public static function unsafeInstantiate() {
        return new NameSource(0);
    }

    private function __construct($i) {
        $this->_i = $i;
    }

    public function getNameAndNext() {
        if ($this->_used) {
            throw new Exception("NameSource can only be used once.");
        }

        $this->_used = true;
        return array
            ( "name" => "input".$this->_i
            , "name_source" => new NameSource($this->_i + 1)
            );
    }
}

/******************************************************************************
 * Formlets are the main abstraction to be used to build forms. First the base
 * class is defined, then some subclasses are defined that are needed to get
 * the stuff to work. Afterwards some primitives to actually build forms are
 * defined.
 */

abstract class Formlet {
    /* Build a renderer and collector from the formlet and also return the 
     * updated name source.
     */
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

    /* Map a function over the input. */
    final public function mapCollector(FunctionValue $transformation) {
        return new MappedCollectorFormlet($this, $transformation);
    }
}


/* A PureFormlet collects a constant value and renderes to an empty string. */
class PureFormlet extends Formlet {
    private $_value; // mixes

    public function __construct(Value $value) {
        $this->_value = $value;
    }

    public function build(NameSource $name_source) {
        return array
            ( "renderer"    => new ConstRenderer("")
            , "collector"   => new ConstCollector($this->_value)
            , "name_source" => $name_source
            );
    }
}

function _pure(Value $value) {
    return new PureFormlet($value); 
}


/* A combined formlets glues to formlets together to a new one. */ 
class CombinedFormlets extends Formlet {
    private $_l; // Formlet
    private $_r; // Formlet

    public function __construct(Formlet $left, Formlet $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function build(NameSource $name_source) {
        $l = $this->_l->build($name_source);
        $r = $this->_r->build($l["name_source"]);
        $collector = combineCollectors($l["collector"], $r["collector"]);
        return array
            ( "renderer"    => new CombinedRenderer($l["renderer"], $r["renderer"])
            , "collector"   => $collector
            , "name_source" => $r["name_source"]
            );
    }
}


/* A checked formlet does a predicate check on the collected value. */
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


/* A formlet where a function is applied to the collected value. */
class MappedCollectorFormlet extends Formlet {
    private $_formlet; // Formlet
    private $_transformation; // Predicate
    
    public function __construct(Formlet $formlet, FunctionValue $transformation) {
        guardHasArity($transformation, 1);
        $this->_formlet = $formlet;
        $this->_transformation = $transformation;
    }

    public function build(NameSource $name_source) {
        $fmlt = $this->_formlet->build($name_source);
        return array( "renderer"    => $fmlt["renderer"]
                    , "collector"   => new MappedCollector( $fmlt["collector"]
                                                          , $this->_transformation
                                                          )
                    , "name_source" => $fmlt["name_source"]
                    );
    }
}


/******************************************************************************
 * This are the primitives to be used to build actual forms.
 */

/* A formlet collecting nothing and rendering a constant string. */
class StaticFormlet extends Formlet {
    private $_content; // string

    public function __construct($content) { 
        guardIsString($content);
        $this->_content = $content;
    }

    public function build(NameSource $name_source) {
        return array
            ( "renderer"    => new ConstRenderer($this->_content)
            , "collector"   => new NullaryCollector()
            , "name_source" => $name_source
            );
    }
}

function _static($content) {
    return new StaticFormlet($content);
}


/* A formlet to input some text. Renders to according HTML and collects a
 * string. This surely needs to be improved. 
 */
class TextInputFormlet extends Formlet {
    protected $_value; // string
    protected $_attributes; // array

    public static $disallowed_attributes = array
        ( "accept"
        , "checked"
        , "form"
        , "formaction"
        , "formenctype"
        , "formmethod"
        , "formnovalidate"
        , "formtarget"
        , "height"
        , "list"
        , "max"
        , "min"
        , "multiple"
        , "name"
        , "src"
        , "step"
        , "value"
        );

    public function __construct($value = null, $attributes = null) {
        if ($value !== null)
            guardIsString($_value);
        $attributes = defaultTo($attributes, array());
        guardIsArray($attributes);
        $disallowed_attributes_used = array_intersect
                                        ( array_keys($attributes)
                                        , self::$disallowed_attributes
                                        );
        if (count($disallowed_attributes_used) > 0) {
            throw new Exception("TextInputFormlet::__construct: "
                               ."The following attributes are not allowed: "
                               .implode(", ", $disallowed_attributes_used)
                               );
        }

        $this->_value = $value; 
        $this->_attributes = $attributes; 
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

    public function renderValues(RenderDict $dict, $args) {
        $name = $args["name"];
        $value = $dict->value($name);
        if ($value === null)
            $value = $this->_value;
        $errors = $dict->errors($name);
        // TODO: this would need some more parameters 
        return "<input type='text' name='$name'"
              .($value !== null ? " value='$value'" : "")
              .keysAndValuesToHTMLAttributes($this->_attributes)
              ."/>"
              .($errors !== null ? "<span class='error'>"
                                        .implode("<br />", $errors)
                                  ."</span>"
                                 : "" 
               );
    }
}

function _text_input() {
    return new TextInputFormlet();
}

?>
