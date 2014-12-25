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


/*************/
/* Collector */
/*************/

abstract class Collector {
}

class ConstCollector extends Collector {
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class ApplyCollector extends Collector {
    private $l;
    private $r;

    public function __construct(Collector $left, Collector $right) {
        $this->l = $left;
        $this->r = $right;
    }
}

class EmptyCollector extends Collector {
}

class StringCollector extends Collector {
    private $name; // string

    public function __construct($name) {
        guardIsName($name);
        $this->name = $name;
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
/* PureValue */ 
/*************/

class PureValueFactory extends FormletFactory {
    public static function instantiate($args) {
        return new PureValue($args[0]); 
    }
}

class PureValue extends Formlet {
    private $value; // mixes

    public function __construct($value) {
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

if ($TEST_MODE) {
    print_check_isFormlet("PureValue", array(42));
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
    $pv = PureValueFactory::instantiate(1337);
    print_check_isFormlet("CombinedFormlets", array($pv, $pv));
    echo "\n";
}


/*****************/
/* StaticSection */
/*****************/

class StaticSectionFactory extends FormletFactory {
    public static function instantiate($args) {
        return new StaticSection($args[0]);
    } 
}

class StaticSection extends Formlet {
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

if ($TEST_MODE) {
    print_check_isFormlet("StaticSection", array("Static"));
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

if ($TEST_MODE) {
    print_check_isFormlet("TextInput", array("Hello World!"));
    echo "\n";
}

?>
