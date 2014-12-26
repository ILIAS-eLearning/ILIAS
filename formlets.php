<?php

/******************************************************************************
/* Copyright (c) 2014 Richard Klees
/*
/* This is an attempt to a PHP implementation of the idea of formlets [1].
/* General idea is to have an abstract and composable representation of forms, 
/* called Formlets, that can be transformed to a concrete Renderer and 
/* Collector. 
/* While the Renderer is responsible for creating an HTML representation of a 
/* Formlet, the Collector is responsible for collection inputs of the user from 
/* the environment.
/*
/* The PHP implementations turns out to be a little more complex, since stuff 
/* like currying and functions as values is not as handy as in functional 
/* languages.
/*
/* [1] http://groups.inf.ed.ac.uk/links/papers/formlets-essence.pdf
/*     The Essence of Form Abstraction (Cooper, Lindley, wadler, Yallop)
*/

global $TEST_MODE;

if ($TEST_MODE === null) {
    $TEST_MODE = true;
}

/************/
/* Renderer */
/************/

abstract class Renderer {
    abstract public function render();
}

class EmptyRenderer extends Renderer {
    public function render() {
        return "";
    }
};

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

class CallbackRenderer extends Renderer {
    private $call_object; // callable
    private $args; // mixed

    public function __construct($call_object, $args) {
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
/* Values work around the problem, that functions could not be used as ordinary
/* values easily in PHP.
/*
/* A value either wraps a plain value in an underlying PHP-Representation or 
/* is a possibly curried function that could be applied to other values.
*/

abstract class Value {
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

    public function __construct($value) {
        $this->value = $value;
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
}

/* Construct a plain value from a PHP value. */
function _plain($value) {
    return new PlainValue($value);
}


final class FunctionValue extends Value {
    private $function_name; // string
    private $call_object; // object
    private $arity; // int
    private $args; // array

    public function __construct($arity, $function_name, $call_object = null, $args = null) {
        $args = defaultTo($args, array());

        guardIsInt($arity);
        guardIsString($function_name);
        guardIsArray($args);
        if ($call_object !== null) guardIsObject($call_object);

        $this->function_name = $function_name;
        $this->call_object = $call_object; 
        $this->arity = $arity;
        $this->args = $args;
    }

    public function get() {
        throw new GetError("FunctionValue");
    } 

    public function apply(Value $to) {
        if ($this->arity > 1) {
            return $this->deferredCall($this->args, $to->get());
        }
        else {
            // EXPERIMENTAL
            if($to->isError()) {
                return $to;
            }

            $val = $this->actualCall($this->args, $to->get());
            return $this->toValue($val);
        }
    }
    
    public function isApplicable() {
        return true;
    }

    public function isError() {
        return false;
    }

    private function deferredCall($args, $next_value) {
        $args[] = $next_value;
        return new FunctionValue( $this->arity - 1
                                , $this->function_name
                                , $this->call_object
                                , $args
                                );
    }

    private function actualCall($args, $last_value) {
        $args[] = $last_value;

        if ($this->call_object === null) {
            return call_user_func_array($this->function_name, $args);
        }
        else {
            return call_user_func_array( array( $this->call_object
                                              , $this->function_name
                                              )
                                       , $args
                                       );
        }
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

function _function($arity, $function_name, $call_object = null) {
    return new FunctionValue($arity, $function_name, $call_object);
}

// EXPERIMENTAL
final class ErrorValue extends Value {
    private $others; // array(ErrorValue)
    private $reason; // string

    public function __construct($reason) {
        $this->others = array();
        $this->reason = $reason;
    }

    public function get() {
        throw new GetError("ErrorValue");
    } 

    public function apply(Value $to) {
        if ($to->isError()) {
            $this->others[] = $to;
        }

        return $this;
    }

    public function isApplicable() {
        return true;
    }

    public function isError() {
        return true;
    }
}


/*************/
/* Collector */
/*************/

abstract class Collector {
    abstract public function collect($env);
}

class ConstCollector extends Collector {
    private $value; // Value

    public function __construct(Value $value) {
        $this->value = $value;
    }

    public function collect($env) {
        return $this->value;
    }
}

class ApplyCollector extends Collector {
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
}

class EmptyCollector extends Collector {
    public function collect($env) {
        // hmm, what is needed here??
        die("EmptyCollector::collect: NYI!");
    }
}

class StringCollector extends Collector {
    private $name; // string

    public function __construct($name) {
        guardIsName($name);
        $this->name = $name;
    }

    public function collect($env) {
        guardIsString($env[$this->name]);
        return _plain($env[$this->name]);
    }
}


/******************/
/* Error Handling */
/******************/

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

/**************/
/* NameSource */
/**************/

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

    public function cmb(Formlet $other) {
        return new CombinedFormlets($this, $other);
    }
}


/****************************/
/* Tests on implementations */
/****************************/

function verboseCheck_isFormlet($name, $args) {
    $name .= "Factory";
    $formlet = $name::instantiate($args);
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
            ( "renderer"    => new EmptyRenderer()
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
        return array
            ( "renderer"    => new CombinedRenderer($l["renderer"], $r["renderer"])
            , "collector"   => new ApplyCollector($l["collector"], $r["collector"]) 
            , "name_source" => $r["name_source"]
            );
    }
}

if ($TEST_MODE) {
    $pv = PureFormletFactory::instantiate(array(new PlainValue(1337)));
    print_check_isFormlet("CombinedFormlets", array($pv, $pv));
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
            , "collector"   => new EmptyCollector()
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
