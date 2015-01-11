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
        ->satisfies(_fn("is_string"), "Input is no string.")
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
        ->wrapCollector(_fn(function($collector) {
            // We don't really need the value, we just
            // have to check weather it is there.
            try {
                $collector->collect();
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


function _with_label($label, Formlet $other) {
    return $other->mapHTML(_fn( function ($_, $html) use ($label) {
        guardIsHTMLTag($html);

        // use inputs name as id, as it is unique
        $name = $html->attribute("name");    
        guardIsString($name);

        return html_concat
                ( tag("label", array("for" => $name), text($label))
                , $html->attribute("id", $name)
                );
    }));   
}

function _with_errors(Formlet $other) {
    return $other->mapHTML(_fn(function ($dict, $html) {
        guardIsHTMLTag($html);
        
        $name = $html->attribute("name");
        guardIsString($name);

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

/* A formlet representing a submit button, possibly collecting a boolean. */
class SubmitButtonFormlet extends Formlet {
    protected $_label; // label 
    protected $_collects; // bool
    protected $_attributes; // string

    public function __construct($label, $collects = false, $attributes = null) {
        guardIsString($label);
        guardIsBool($collects);
        if ($attributes !== null)
            guardIsArray($attributes);
        $this->_label= $label; 
        $this->_collects= $collects; 
        $this->_attributes = $attributes;
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        $name = $res["name"];
        $collector = $this->_collects
                    ? new ExistsCollector($name)
                    : new NullaryCollector()
                    ;
        return array
            ( "builder"    => new TagBuilder( "submit_button"
                                            , _fn(function($a) use ($name) {
                                                    return $this->getAttributes($name, $a);
                                                })
                                            , _const(null)
                                            )
            , "collector"   => $collector
            , "name_source" => $res["name_source"]
            );
    }

    public function getAttributes($name, RenderDict $dict) {
        $attributes = id($this->_attributes);
        if ($this->_collects)
            $attributes["name"] = $name; 
        $attributes["value"] = $this->_label; 
        return $attributes;
    }
}

function _submit($label, $collects = false, $attributes = null) {
    return new SubmitButtonFormlet($label, $collects, $attributes);
}


?>
