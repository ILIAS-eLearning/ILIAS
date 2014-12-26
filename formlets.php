<?php

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


/*********/
/* Value */
/*********/

abstract class Value {
    abstract public function get();
    abstract public function apply(Value $to);
    abstract public function isApplicable();
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

final class ConstValue extends Value {
    private $value; //mixed

    public function __construct($value) {
        $this->value = $value;
    }

    public function get() {
        return $this->value;
    }

    public function apply(Value $to) {
        throw new ApplyError("ConstValue", "any Value");
    }

    public function isApplicable() {
        return false;
    }

    public function isError() {
        return false;
    }
}

function _const($value) {
    return new ConstValue($value);
}

final class FunctionValue extends Value {
    private $function_name; // string
    private $call_object; // object

    public function __construct($function_name, $call_object = null) {
        $this->function_name = $function_name;
        $this->call_object = $call_object; 
    }

    public function get() {
        throw new GetError("FunctionValue");
    } 

    public function apply(Value $to) {
        if($to->isError()) {
            return $to;
        }

        if ($to->isApplicable()) {
            throw new ApplyError("FunctionValue", typeName($to));
        }

        $fn = $this->function_name;
        $obj = $this->call_object;

        if ($obj === null) {
            return $fn($to->get());
        }
        else {
            return $obj->$fn($to->get());
        }
    }

    public function isApplicable() {
        return true;
    }

    public function isError() {
        return false;
    }
}

function _function($function_name, $call_object = null) {
    return new FunctionValue($function_name, $call_object);
}


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
        if ($to->isError() {
            $this->$others[] = $to;
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
        return _const($env[$this->name]);
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

function guardIsName($arg) {
    guardIsString($arg);
    // ToDo: implement properly
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
    print_check_isFormlet("PureFormlet", array(new ConstValue(42)));
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
    $pv = PureFormletFactory::instantiate(array(new ConstValue(1337)));
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
