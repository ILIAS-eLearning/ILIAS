<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Formlets are the main abstraction to be used to build forms. First the base
 * class is defined, then some subclasses are defined that are needed to get
 * the stuff to work. Afterwards some primitives to actually build forms are
 * defined.
 */

require_once("checking.php");
require_once("values.php");
require_once("builders.php");
require_once("collectors.php");
require_once("namesource.php");

abstract class Formlet {
    /* Build a builder and collector from the formlet and also return the 
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


/* A PureFormlet collects a constant value and buildes to an empty string. */
class PureFormlet extends Formlet {
    private $_value; // mixes

    public function __construct(Value $value) {
        $this->_value = $value;
    }

    public function build(NameSource $name_source) {
        return array
            ( "builder"    => new TextBuilder("")
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
            ( "builder"    => new CombinedBuilder($l["builder"], $r["builder"])
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
        return array( "builder"    => $fmlt["builder"]
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
        return array( "builder"    => $fmlt["builder"]
                    , "collector"   => new MappedCollector( $fmlt["collector"]
                                                          , $this->_transformation
                                                          )
                    , "name_source" => $fmlt["name_source"]
                    );
    }
}


/* A formlet collecting nothing and building a constant string. */
class TextFormlet extends Formlet {
    private $_content; // string

    public function __construct($content) { 
        guardIsString($content);
        $this->_content = $content;
    }

    public function build(NameSource $name_source) {
        return array
            ( "builder"    => new TextBuilder($this->_content)
            , "collector"   => new NullaryCollector()
            , "name_source" => $name_source
            );
    }
}

function _text($content) {
    return new TextFormlet($content);
}


/* A simple html input. */
class InputFormlet extends Formlet implements TagBuilderCallbacks {
    protected $_attributes;

    public function __construct($attributes) {
        guardEachAndKeys($attributes, "guardIsString", "guardIsString");
        $this->_attributes = $attributes;
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "text_input", $this, $res["name"])
            , "collector"   => new StringCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getContent(RenderDict $dict, $name) {
        return null; 
    }

    public function getAttributes(RenderDict $dict, $name) {
        $attributes = id($this->_attributes);
        $attributes["name"] = $name; 
        return $attributes; 
    }
}

function _input($attributes = array()) {
    return new InputFormlet($attributes);
}

?>
