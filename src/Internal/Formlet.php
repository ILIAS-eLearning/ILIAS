<?php

/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\IFormlet;
use Lechimp\Formlets\IValue;
use Lechimp\Formlets\Internal\Checking as C;

abstract class Formlet implements IFormlet {
    /* Build a builder and collector from the formlet and also return the 
     * updated name source.
     */
    abstract public function instantiate(NameSource $name_source);
    
    /* Combine this formlet with another formlet. Yields a new formlet. */
    final public function cmb(IFormlet $other) {
        return new CombinedFormlets($this, $other);
    }

    /* Get a new formlet with an additional check of a predicate on the input
     * to the formlet and an error message for the case the predicate fails.
     */
    final public function satisfies(IValue $predicate, $error) {
        return $this->mapBC( _id()
                           , _fn( function ($collector) use ($predicate, $error) {
                                return $collector->satisfies($predicate, $error);
                           }));
    }

    /* Map a function over the input value. */
    final public function map(IValue $transformation) {
        return $this->mapBC( _id()
                            , _fn( function($collector) use ($transformation) {
                                return $collector->map($transformation);
                            }));
    }

    /* Wrap a function around the collector. */
    final public function wrapCollector(IValue $wrapper) {
        return $this->mapBC( _id()
                           , _fn( function($collector) use ($wrapper) {
                                return $collector->wrap($wrapper);
                           }));
    }

    /* Replace the collector. */
    final public function replaceCollector(Collector $collector) {
        return $this->mapBC( _id()
                           , _fn( function($_) use ($collector) {
                                return $collector;
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

    static function pure(Value $value) {
        return new PureFormlet($value); 
    }
}


/* A PureFormlet collects a constant value and buildes to an empty string. */
class PureFormlet extends Formlet {
    private $_value; // mixes

    public function __construct(Value $value) {
        $this->_value = $value;
    }

    public function instantiate(NameSource $name_source) {
        return array
            ( "builder"    => new TextBuilder("")
            , "collector"   => new ConstCollector($this->_value)
            , "name_source" => $name_source
            );
    }
}

/* A combined formlets glues to formlets together to a new one. */ 
class CombinedFormlets extends Formlet {
    private $_l; // Formlet
    private $_r; // Formlet

    public function __construct(Formlet $left, Formlet $right) {
        $this->_l = $left;
        $this->_r = $right;
    }

    public function instantiate(NameSource $name_source) {
        $l = $this->_l->instantiate($name_source);
        $r = $this->_r->instantiate($l["name_source"]);
        $collector = Collector::combineCollectors($l["collector"], $r["collector"]);
        return array
            ( "builder"    => new CombinedBuilder($l["builder"], $r["builder"])
            , "collector"   => $collector
            , "name_source" => $r["name_source"]
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
        C::guardHasArity($transform_builder, 1);
        C::guardHasArity($transform_collector, 1);
        $this->_formlet = $formlet;
        $this->_transform_builder = $transform_builder; 
        $this->_transform_collector = $transform_collector;
    }

    public function instantiate(NameSource $name_source) {
        $fmlt = $this->_formlet->instantiate($name_source);
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
        C::guardIsString($content);
        $this->_content = $content;
    }

    public function instantiate(NameSource $name_source) {
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
        C::guardEachAndKeys($attributes, "guardIsString", "guardIsString");
        $this->_attributes = $attributes;
    }

    public function instantiate(NameSource $name_source) {
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
    C::guardIsString($type);
    $attributes["type"] = $type;
    return new InputFormlet($attributes);
}

/* A formlet to input some text in an area. */
class TextAreaFormlet extends Formlet implements TagBuilderCallbacks {
    protected $_attributes; // string

    public function __construct($attributes = null) {
        if ($attributes !== null)
            C::guardIsArray($attributes);
        $this->_attributes = $attributes; 
    }

    public function instantiate(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "textarea", $this, $res["name"] )
            , "collector"   => new AnyCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getContent(RenderDict $dict, $name) {
        return H::text("");
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

function _textual_input($type, $default = null, $attributes = null) {
    C::guardIfNotNull($default, "guardIsString");
    return _input($type, $attributes)
        // Only accept string inputs
        //->satisfies(_fn("is_string"), "Input is no string.")
        // Set value by input or given value if there is no input. 
        ->mapHTML(_fn(function ($dict, $html) use ($default) {
            $name = $html->attribute("name");

            $value = $dict->value($name);
            if ($value === null)
                $value == $default;
            if ($value !== null)
                $html = $html->attribute("value", $value);

            return $html;
        }));
}

function _text_input($default = null, $attributes = null) {
    return _textual_input("text", $default, $attributes);
}


function _textarea($default = null, $attributes = null) {
    C::guardIfNotNull($default, "guardIsString");
    return _textarea_raw($attributes)
        ->satisfies(_fn("is_string"), "Input is no string.")
        ->mapHTML(_fn(function ($dict, $html) use ($default) {
            $name = $html->attribute("name");
            
            $value = $dict->value($name);
            if ($value === null)
                $value = $default;
            if ($value !== null)
                $html = $html->content(H::text($value));

            return $html;
        }));
}


function _checkbox($default = false, $attributes = null) {
    C::guardIsBool($default);
    return _input("checkbox", $attributes)
        ->wrapCollector(_fn(function($collector, $inp) {
            // We don't really need the value, we just
            // have to check weather it is there.
            try {
                $collector->collect($inp);
                return true;
            }
            catch (MissingInputError $e) {
                return false;
            }
        }))
        ->mapHTML(_fn(function ($dict, $html) use ($default) {
            $name = $html->attribute("name");

            if ($dict->isEmpty())
                $value = $default;
            else
                $value = $dict->value($name) !== null;
            if ($value)
                return $html->attribute("checked", "checked");
            return $html;
        })); 
} 

function _submit($value, $attributes = array(), $collects = false) {
    $attributes["value"] = $value;
    $input = _input("submit", $attributes);

    if ($collects) {
        return $input->wrapCollector(_fn(function($collector, $inp) {
            try {
                $collector->collect($inp);
                return true;
            } 
            catch (MissingInputError $e) {
                return false;
            }
        }));
    }
    else {
        return $input->replaceCollector( new NullaryCollector() );
    }
}

function _button($value, $attributes = array()) {
    $attributes["value"] = $value;
    return _input("button", $attributes)
            ->replaceCollector( new NullaryCollector() )
            ;
}

// TODO: Missing HTML-input type=color. What is the expected format of value for
// a color?

// TODO: Missing HTML-input type=date. What is the expected format of value for
// a date?

// TODO: Missing HTML-input type=datetime. What is the expected format of value 
// for a datetime?

// TODO: Missing HTML-input type=datetime-local. What is the expected format of 
// value for a datetime-local?

function _email($default = null, $attributes = array()) {
    return _textual_input("email", $default, $attributes);
}

// TODO: Missing HTML-input type=file. I would need to make the $_FILES array 
// accessible, right?

function _hidden($value, $attributes = array()) {
    $attributes["value"] = $value;
    return _input("hidden", $attributes);
}

// TODO: Missing HTML-input type=image. Do i really need this?

// TODO: Missing HTML-input type=month. What is the expected format of value 
// for a datetime-local? Do i really need it?

function _number($value, $min, $max, $step, $attributes = array()
                , $error_int, $error_range, $error_step
                ) {
    C::guardIsInt($value);
    C::guardIsInt($min);
    C::guardIsInt($max);
    C::guardIsInt($step);
    $attributes["value"] = "$value";
    $attributes["min"] = "$min";
    $attributes["max"] = "$max";
    $attributes["step"] = "$step";
    return _input("number", $attributes)
            ->satisfies(_fn("is_numeric", 1), $error_int)
            ->map(_fn("intval", 1))
            ->satisfies(_inRange($min, $max), $error_range)
            ->satisfies(_isMultipleOf($step), $error_step)
            ;
}

function _password($default = null, $attributes = array()) {
    return _textual_input("password", $default, $attributes);
}

function _radio($options, $default = null, $attributes = array()
               , $attributes_options = array()) {
    C::guardEach($options, "guardIsString");
    if ($default === null) {
        $default = $options[0];
    }
    return _input("radio", $attributes)
        ->mapHTML(_fn(function($dict, $html) use ($options, $attributes, $default) {
            $name = $html->attribute("name");

            $value = $dict->value($name);
            if ($value === null)
                $value = $default;

            $attributes_options["name"] = $name;
            $attributes_options["type"] = "radio";
            $counter = 0;
            $make_radios = function($option) 
                           use (&$counter, $name, $value, $attributes_options) {
                $id = $name."_".$counter; 
                $counter++;
                $attributes_options["id"] = $id;
                $attributes_options["value"] = $option;
                if ($option === $value) {
                    $attributes_options["checked"] = "checked";
                }
                return H::tag("li", array(), H::harray(array(
                            H::tag("input", $attributes_options),
                            H::tag("label", array("for" => $id), html_text($option)))));
            };
            $options_html = array_map($make_radios, $options);

            if (!array_key_exists("class", $attributes)) {
                $attributes["class"] = "radiogroup";
            }
            # TODO: This will produce invalid HTML, as the ol attribute does not
            # support the name attribute. We still need it for with_label. 
            $attributes["name"] = $name;
            return H::tag("ol", $attributes, H::harray($options_html));
        }))
        ->satisfies(_fn(function($value) use ($options) {
            return in_array($value, $options);
        }), "Option not available.")
        ;
}

// TODO: Missing HTML-input type=range. What is the expected format of value 
// for a range?

function _reset($value) {
    $attributes["value"] = $value;
    return _input("reset", $attributes)
            ->replaceCollector( new NullaryCollector() )
            ;
}

function _search($default = null, $attributes = array()) {
    return _textual_input("search", $default, $attributes);
}

// Missing HTML-input type=tel. No browser seems to implement it...

// TODO: Missing HTML-input type=time. What is the expected format of value 
// for a time?

function _url($default = null, $attributes = array()) {
    return _textual_input("url", $default, $attributes);
}

// TODO: Missing HTML-input type=week. What is the expected format of value 
// for a week?

function _select($options, $default = null, $attributes = array()) {
    C::guardEach($options, "guardIsString");
    return _input("select", $attributes)
        ->mapHTML(_fn(function($dict, $html) use ($options, $attributes, $default) {
            $name = $html->attribute("name");

            $value = $dict->value($name);
            if ($value === null)
                $value == $default;

            $attributes["name"] = $name;
            $options_html = array_map(function($option) use ($value) {
                if ($option !== $value) {
                    return H::tag("option", array(), html_text($option));
                }
                else {
                    return H::tag("option", array("selected" => "selected"), html_text($option));
                }
            }, $options);
            return H::tag("select", $attributes, H::harray($options_html));
        }))
        ->satisfies(_fn(function($value) use ($options) {
            return in_array($value, $options);
        }), "Option not available.")
        ;
}

/* A formlet that wraps other formlets in a field set */
function _fieldset($legend, Formlet $formlet
                  , $attributes = array(), $legend_attributes = array()) {
    return $formlet
        ->mapHTML(_fn(function ($dict, $html) 
                      use ($legend, $attributes, $legend_attributes) {

            return H::tag("fieldset", $attributes, 
                        H::concat(
                              H::tag("legend", $legend_attributes, 
                                H::text($legend))
                            , $html
                        )
                    );

        }));
} 

/**
 * Apply $fn to all tags that have a name attribute in $html.
 *
 * This transforms the html in place, that is _mute_ it. If $fn returns some 
 * value except for null, it will only be applied to the first named tag.
 */
function html_apply_to_depth_first_name(HTML $html, FunctionValue $fn) {
    return $html->depthFirst(
                        _fn(function($html) {
                            return $html instanceof HTMLTag
                                && $html->attribute("name");
                        }),
                        $fn);
}

/**
 * Returns the name of the first tag with name attribute in $html.
 */
function html_get_depth_first_name(HTML $html) {
    return html_apply_to_depth_first_name($html,
                        _fn(function($html) {
                            return $html->attribute("name");
                        }));
}

function _with_label($label, Formlet $other) {
    return $other->mapHTML(_fn( function ($_, $html) use ($label) {
        // use inputs name as id, as it is unique
        $name = H::get_depth_first_name($html);
        if ($name === null) {
            throw new Exception("_with_label applied to un-named Formlet.");
        }

        // This applies the transformation in place!
        H::apply_to_depth_first_name($html, _fn(function($html) use ($name) {
            $html->attribute("id", $name);
            return true;
        }));

        return H::concat(
                    H::tag("label", array("for" => $name), html_text($label)),
                    $html
                );
    }));   
}

function _with_errors(Formlet $other) {
    return $other->mapHTML(_fn(function ($dict, $html) {
        $name = H::get_depth_first_name($html);
        if ($name === null) {
            throw new Exception("_with_errors applied to un-named Formlet.");
        }

        $errors = $dict->errors($name);
        if ($errors === null)
            return $html;

        foreach ($errors as $error) {
            $html = H::concat
                        ( $html
                        , H::tag("span", array("class" => "error"), html_text($error))
                        );
        }

        return $html;
    }));
}

function id($val) {
    return $val;
}

function _id() {
    static $fn = null;
    if ($fn === null) {
        $fn = _fn("id");
    }
    return $fn;
}

?>
