<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This are the primitives to be used to build actual forms.
 */

require_once("checking.php");
require_once("helpers.php");
require_once("base.php");

/* A formlet to input some text. Renders to according HTML and collects a
 * string.
 */
class TextInputFormlet extends Formlet {
    protected $_value; // string
    protected $_label; // string
    protected $_attributes; // array 

    public function __construct($label = null, $value = null, $attributes = null) {
        if ($label !== null)
            guardIsString($label);
        if ($value !== null)
            guardIsString($value);
        if ($attributes !== null)
            guardIsArray($attributes);
        $this->_label = $label; 
        $this->_value = $value; 
        $this->_attributes = $attributes; 
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "text_input"
                                            , _method(1, $this, "getAttributes", array($res["name"]))
                                            , _const(null)
                                            )
            , "collector"   => new StringCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getAttributes($name, RenderDict $dict) {
        $attributes = id($this->_attributes);
        $attributes["name"] = $name; 

        $value = $dict->value($name);
        if ($value === null)
            $value = $this->_value;
        if ($value !== null)
            $attributes["value"] = $value;

        if ($this->_label !== null)
            $attributes["label"] = $this->_label;

        $errors = $dict->errors($name);
        if ($errors !== null)
            $attributes["errors"] = $errors;
        return $attributes; 
    }
}

function _text_input($label = null, $value = null, $attributes = null) {
    return new TextInputFormlet($label, $value, $attributes);
}

/* A formlet to input some text in an area. */
class TextAreaFormlet extends Formlet {
    protected $_value; // string
    protected $_label; // string
    protected $_attributes; // string

    public function __construct($label = null, $value = null, $attributes = null) {
        if ($label !== null)
            guardIsString($label);
        if ($value !== null)
            guardIsString($value);
        if ($attributes !== null)
            guardIsArray($attributes);
        $this->_label = $label; 
        $this->_value = $value; 
        $this->_attributes = $attributes; 
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "textarea"
                                            , _method(1, $this, "getAttributes", array($res["name"]))
                                            , _method(1, $this, "getContent", array($res["name"]))
                                            )
            , "collector"   => new StringCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getContent($name, RenderDict $dict) {
        $value = $dict->value($name);
        if ($value === null)
            $value = $this->_value;

        return $value !== null ? html_text($value) : html_nop();
    }

    public function getAttributes($name, RenderDict $dict) {
        $attributes = id($this->_attributes);
        $attributes["name"] = $name; 
        
        if ($this->_label !== null)
            $attributes["label"] = $this->_label;

        $errors = $dict->errors($name);
        if ($errors !== null)
            $attributes["errors"] = $errors;
        return $attributes; 
    }
}

function _textarea($label = null, $value = null, $attributes = null) {
    return new TextAreaFormlet($label, $value, $attributes);
}


/* A formlet that wraps other formlets in a field set */
function _fieldset($legend, Formlet $formlet, $attributes = array()) {
    $ret = _text("<fieldset".keysAndValuesToHTMLAttributes($attributes).">");
    if ($legend !== null) {
        $ret = $ret->cmb(_text("<legend>$legend</legend>"));
    }
    return $ret->cmb($formlet)
               ->cmb(_text("</fieldset>"))
               ;
} 

/* A formlet to a boolean via a checkbox. Renders to according HTML and collects
 * a bool.
 */
class CheckboxFormlet extends Formlet {
    protected $_value; // bool 
    protected $_label; // string
    protected $_attributes; // string

    public function __construct($label = null, $value = false, $attributes = null) {
        if ($label !== null)
            guardIsString($label);
        guardIsBool($value);
        if ($attributes !== null)
            guardIsArray($attributes);
        $this->_label = $label; 
        $this->_value = $value; 
        $this->_attributes = $attributes; 
    }

    public function build(NameSource $name_source) {
        $res = $name_source->getNameAndNext();
        return array
            ( "builder"    => new TagBuilder( "checkbox"
                                            , _method(1, $this, "getAttributes", array($res["name"]))
                                            , _const(null)
                                            )
            , "collector"   => new ExistsCollector($res["name"])
            , "name_source" => $res["name_source"]
            );
    }

    public function getAttributes($name, RenderDict $dict) {
        $attributes = id($this->_attributes);
        $attributes["name"] = $name; 
        
        if ($dict->isEmpty())
            $value = $this->_value;
        else
            $value = $dict->value($name) !== null;
        if ($value)
            $attributes["checked"] = null; 
                
        if ($this->_label !== null)
            $attributes["label"] = $this->_label;

        $errors = $dict->errors($name);
        if ($errors !== null)
            $attributes["errors"] = $errors;
        return $attributes;
    }
}

function _checkbox($label = null, $value = false, $attributes = null) {
    return new CheckboxFormlet($label, $value, $attributes);
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
        $collector = $this->_collects
                    ? new ExistsCollector($res["name"])
                    : new NullaryCollector()
                    ;
        return array
            ( "builder"    => new TagBuilder( "submit_button"
                                            , _method(1, $this, "getAttributes", array($res["name"]))
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
