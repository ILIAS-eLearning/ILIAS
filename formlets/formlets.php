<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This are the primitives to be used to build actual forms.
 */

require_once("checking.php");
require_once("helpers.php");
require_once("base.php");


function _text_input($default = null, $attributes = null) {
    guardIfNotNull($default, "guardIsString");
    return _input("text", $attributes)
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


function _textarea($default = null, $attributes = null) {
    guardIfNotNull($default, "guardIsString");
    return _textarea_raw($attributes)
        ->satisfies(_fn("is_string"), "Input is no string.")
        ->mapHTML(_fn(function ($dict, $html) use ($default) {
            $name = $html->attribute("name");
            
            $value = $dict->value($name);
            if ($value === null)
                $value = $default;
            if ($value !== null)
                $html = $html->content(html_text($value));

            return $html;
        }));
}


function _checkbox($default = false, $attributes = null) {
    guardIsBool($default);
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

/* A formlet that wraps other formlets in a field set */
function _fieldset($legend, Formlet $formlet
                  , $attributes = array(), $legend_attributes = array()) {
    return $formlet
        ->mapHTML(_fn(function ($dict, $html) 
                      use ($legend, $attributes, $legend_attributes) {

            return html_tag("fieldset", $attributes, 
                        html_concat(
                              html_tag("legend", $legend_attributes, 
                                html_text($legend))
                            , $html
                        )
                    );

        }));
} 

function html_apply_to_depth_first_name(HTML $html, FunctionValue $fn) {
    return $html->depthFirst(
                        _fn(function($html) {
                            return $html instanceof HTMLTag
                                && $html->attribute("name");
                        }),
                        $fn);
}

function html_get_depth_first_name(HTML $html) {
    return html_apply_to_depth_first_name($html,
                        _fn(function($html) {
                            return $html->attribute("name");
                        }));
}

function _with_label($label, Formlet $other) {
    return $other->mapHTML(_fn( function ($_, $html) use ($label) {
        // use inputs name as id, as it is unique
        $name = html_get_depth_first_name($html);
        if ($name === null) {
            throw new Exception("_with_label applied to un-named Formlet.");
        }

        return html_concat(
                    html_tag("label", array("for" => $name), html_text($label)),
                    html_apply_to_depth_first_name($html, _fn(function($html) use ($name) {
                        $html->attribute("id", $html->attribute("name"));
                        return $html;
                    }))
                );
    }));   
}

function _with_errors(Formlet $other) {
    return $other->mapHTML(_fn(function ($dict, $html) {
        $name = html_get_depth_first_name($html);
        if ($name === null) {
            throw new Exception("_with_errors applied to un-named Formlet.");
        }

        $errors = $dict->errors($name);
        if ($errors === null)
            return $html;

        foreach ($errors as $error) {
            $html = html_concat
                        ( $html
                        , html_tag("span", array("class" => "error"), html_text($error))
                        );
        }

        return $html;
    }));
}

?>
