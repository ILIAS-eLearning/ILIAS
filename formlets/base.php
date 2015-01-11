<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Formlets are the main abstraction to be used to build forms. First the base
 * class is defined, then some subclasses are defined that are needed to get
 * the stuff to work. Afterwards some primitives to actually build forms are
 * defined. These resembles the interface presented in the paper.
 * Some more complex and usable examples are given in formlets.php.
 */

require_once("checking.php");
require_once("helpers.php");
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

    /* Map a function over the input value. */
    final public function map(FunctionValue $transformation) {
        return $this->mapBC( _id()
                            , _fn( function($collector) use ($transformation) {
                                return $collector->map($transformation);
                            }));
    }

    /* Wrap a function around the collector. */
    final public function wrapCollector(FunctionValue $wrapper) {
        return $this->mapBC( _id()
                           , _fn( function($collector) use ($wrapper) {
                                return $collector->wrap($wrapper);
                           }));
    }

    /* Map a function over the build HTML. */
    final public function mapHTML(FunctionValue $transformation) {
        return $this->mapBC( _fn( function($builder) use ($transformation) {
                                return $builder->map($transformation);
                            })
                            , _id()
                            );
    }

    /* Map a function over the builder and collector. */
    final public function mapBC( FunctionValue $transform_builder
                                , FunctionValue $transform_collector ) {
        return new MappedFormlet($this, $transform_builder, $transform_collector);
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


/* A formlet where a function is applied to buiid builder and collector. */
class MappedFormlet extends Formlet {
    private $_formlet; // Formlet
    private $_transform_builder; // FunctionValue 
    private $_transform_collector; // FunctionValue 
    
    public function __construct( Formlet $formlet
                               , FunctionValue $transform_builder
                               , FunctionValue $transform_collector ) {
        guardHasArity($transform_builder, 1);
        guardHasArity($transform_collector, 1);
        $this->_formlet = $formlet;
        $this->_transform_builder = $transform_builder; 
        $this->_transform_collector = $transform_collector;
    }

    public function build(NameSource $name_source) {
        $fmlt = $this->_formlet->build($name_source);
        $b = $this->_transform_builder
                ->apply(_val($fmlt["builder"]))
                ->get();
        $c = $this->_transform_collector
                ->apply(_val($fmlt["collector"]))
                ->get();
        return array( "builder"    => $b
                    , "collector"   => $c
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
            ( "builder"    => new TagBuilder( "input", $this, $res["name"])
            , "collector"   => new AnyCollector($res["name"])
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

function _input($type, $attributes = array()) {
    guardIsString($type);
    $attributes["type"] = $type;
    return new InputFormlet($attributes);
}

/* A formlet to input some text in an area. */
class TextAreaFormlet extends Formlet implements TagBuilderCallbacks {
    protected $_attributes; // string

    public function __construct($attributes = null) {
        if ($attributes !== null)
            guardIsArray($attributes);
        $this->_attributes = $attributes; 
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "textarea", $this, $res["name"] )
            , "collector"   => new AnyCollector($res["name"])
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

function _textarea_raw($attributes = null) {
    return new TextAreaFormlet($attributes);
}


?>
