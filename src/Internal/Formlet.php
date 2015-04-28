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
use Lechimp\Formlets\Internal\Values as V;
use Lechimp\Formlets\Internal\HTML as H;

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
        return $this->mapBC( self::id()
                           , V::fn(function ($collector) use ($predicate, $error) {
                                return $collector->satisfies($predicate, $error);
                           }));
    }

    /* Map a function over the input value. */
    final public function map(IValue $transformation) {
        return $this->mapBC( self::id()
                            , V::fn(function($collector) use ($transformation) {
                                return $collector->map($transformation);
                            }));
    }

    /* Wrap a function around the collector. */
    final public function wrapCollector(IValue $wrapper) {
        return $this->mapBC( self::id()
                           , V::fn(function($collector) use ($wrapper) {
                                return $collector->wrap($wrapper);
                           }));
    }

    /* Replace the collector. */
    final public function replaceCollector(Collector $collector) {
        return $this->mapBC( self::id()
                           , V::fn(function($_) use ($collector) {
                                return $collector;
                           }));
    }

    /* Map a function over the build HTML. */
    final public function mapHTML(FunctionValue $transformation) {
        return $this->mapBC( V::fn(function($builder) use ($transformation) {
                                return $builder->map($transformation);
                            })
                            , self::id()
                            );
    }

    /* Map a function over the builder and collector. */
    final public function mapBC( FunctionValue $transform_builder
                                , FunctionValue $transform_collector ) {
        return new MappedFormlet($this, $transform_builder, $transform_collector);
    }

    static public function pure(Value $value) {
        return new PureFormlet($value); 
    }

    static public function text($content) {
        return new TextFormlet($content);
    }

    static public function input($type, $attributes = array()) {
        C::guardIsString($type);
        $attributes["type"] = $type;
        return new InputFormlet($attributes);
    }

    static public function textarea_raw($attributes = null) {
        return new TextAreaFormlet($attributes);
    }

    static public function textual_input($type, $default = null, $attributes = null) {
        C::guardIfNotNull($default, "guardIsString");
        return self::input($type, $attributes)
            // Only accept string inputs
            //->satisfies(V::fn("is_string"), "Input is no string.")
            // Set value by input or given value if there is no input. 
            ->mapHTML(V::fn(function ($dict, $html) use ($default) {
                $name = $html->attribute("name");

                $value = $dict->value($name);
                if ($value === null)
                    $value == $default;
                if ($value !== null)
                    $html = $html->attribute("value", $value);

                return $html;
            }));
    }

    static public function text_input($default = null, $attributes = null) {
        return self::textual_input("text", $default, $attributes);
    }


    static public function textarea($default = null, $attributes = null) {
        C::guardIfNotNull($default, "guardIsString");
        return self::textarea_raw($attributes)
            ->satisfies(V::fn("is_string"), "Input is no string.")
            ->mapHTML(V::fn(function ($dict, $html) use ($default) {
                $name = $html->attribute("name");
                
                $value = $dict->value($name);
                if ($value === null)
                    $value = $default;
                if ($value !== null)
                    $html = $html->content(H::text($value));

                return $html;
            }));
    }


    static public function checkbox($default = false, $attributes = null) {
        C::guardIsBool($default);
        return self::input("checkbox", $attributes)
            ->wrapCollector(V::fn(function($collector, $inp) {
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
            ->mapHTML(V::fn(function ($dict, $html) use ($default) {
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

    static public function submit($value, $attributes = array(), $collects = false) {
        $attributes["value"] = $value;
        $input = self::input("submit", $attributes);

        if ($collects) {
            return $input->wrapCollector(V::fn(function($collector, $inp) {
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

    static public function button($value, $attributes = array()) {
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

    static public function email($default = null, $attributes = array()) {
        return self::textual_input("email", $default, $attributes);
    }

    // TODO: Missing HTML-input type=file. I would need to make the $_FILES array 
    // accessible, right?

    static public function hidden($value, $attributes = array()) {
        $attributes["value"] = $value;
        return _input("hidden", $attributes);
    }

    // TODO: Missing HTML-input type=image. Do i really need this?

    // TODO: Missing HTML-input type=month. What is the expected format of value 
    // for a datetime-local? Do i really need it?

    static public function number($value, $min, $max, $step, $attributes = array()
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
                ->satisfies(V::fn("is_numeric", 1), $error_int)
                ->map(V::fn("intval", 1))
                ->satisfies(_inRange($min, $max), $error_range)
                ->satisfies(_isMultipleOf($step), $error_step)
                ;
    }

    static public function password($default = null, $attributes = array()) {
        return self::textual_input("password", $default, $attributes);
    }

    static public function radio($options, $default = null, $attributes = array()
                   , $attributes_options = array()) {
        C::guardEach($options, "guardIsString");
        if ($default === null) {
            $default = $options[0];
        }
        return self::input("radio", $attributes)
            ->mapHTML(V::fn(function($dict, $html) use ($options, $attributes, $default) {
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
            ->satisfies(V::fn(function($value) use ($options) {
                return in_array($value, $options);
            }), "Option not available.")
            ;
    }

    // TODO: Missing HTML-input type=range. What is the expected format of value 
    // for a range?

    static public function reset($value) {
        $attributes["value"] = $value;
        return _input("reset", $attributes)
                ->replaceCollector( new NullaryCollector() )
                ;
    }

    static public function search($default = null, $attributes = array()) {
        return self::textual_input("search", $default, $attributes);
    }

    // Missing HTML-input type=tel. No browser seems to implement it...

    // TODO: Missing HTML-input type=time. What is the expected format of value 
    // for a time?

    static public function url($default = null, $attributes = array()) {
        return self::textual_input("url", $default, $attributes);
    }

    // TODO: Missing HTML-input type=week. What is the expected format of value 
    // for a week?

    static public function select($options, $default = null, $attributes = array()) {
        C::guardEach($options, "guardIsString");
        return self::input("select", $attributes)
            ->mapHTML(V::fn(function($dict, $html) use ($options, $attributes, $default) {
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
            ->satisfies(V::fn(function($value) use ($options) {
                return in_array($value, $options);
            }), "Option not available.")
            ;
    }

    /* A formlet that wraps other formlets in a field set */
    static public function fieldset($legend, Formlet $formlet
                      , $attributes = array(), $legend_attributes = array()) {
        return $formlet
            ->mapHTML(V::fn(function ($dict, $html) 
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
    // TODO: This should propably go to HTML.
    static public function html_apply_to_depth_first_name(HTML $html, FunctionValue $fn) {
        return $html->depthFirst(
                            V::fn(function($html) {
                                return $html instanceof HTMLTag
                                    && $html->attribute("name");
                            }),
                            $fn);
    }

    /**
     * Returns the name of the first tag with name attribute in $html.
     */
    // TODO: This should propably go to HTML.
    static public function html_get_depth_first_name(HTML $html) {
        return html_apply_to_depth_first_name($html,
                            V::fn(function($html) {
                                return $html->attribute("name");
                            }));
    }

    static public function _with_label($label, Formlet $other) {
        return $other->mapHTML(V::fn(function ($_, $html) use ($label) {
            // use inputs name as id, as it is unique
            $name = self::get_depth_first_name($html);
            if ($name === null) {
                throw new Exception("_with_label applied to un-named Formlet.");
            }

            // This applies the transformation in place!
            self::apply_to_depth_first_name($html, V::fn(function($html) use ($name) {
                $html->attribute("id", $name);
                return true;
            }));

            return H::concat(
                        H::tag("label", array("for" => $name), html_text($label)),
                        $html
                    );
        }));   
    }

    static public function with_errors(Formlet $other) {
        return $other->mapHTML(V::fn(function ($dict, $html) {
            $name = self::get_depth_first_name($html);
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

    static public function _id($v) {
        return $v; 
    }

    static public function id() {
        static $fn = null;
        if ($fn === null) {
            $fn = V::fn(function($v) { return $v; });
        }
        return $fn;
    }
}


