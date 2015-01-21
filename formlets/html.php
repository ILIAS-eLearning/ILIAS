<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Representation of html entities. This does not in any way guarantee to 
 * produce valid HTML or something.
 */

require_once("checking.php");
require_once("helpers.php");
require_once("values.php");

abstract class HTML {
    /**
     * Render a string from the HTML.
     */
    abstract public function render();

    /**
     * Get a new HTML by concatenating $this and $other.
     */
    public function concat(HTML $right) {
        return html_concat($this, $right);
    }

    /**
     * Make a depth first search.
     * Performs $transformation on every node that matches $predicate. If 
     * $transformation returns null, search will go on, if it returns a 
     * value, search will stop and return the value.
     */
    public function depthFirst( FunctionValue $predicate
                              , FunctionValue $transformation) {
        guardHasArity($predicate, 1); 
        guardHasArity($transformation, 1); 
        if ($predicate->apply(_val($this))->get()) {
            $res = $transformation->apply(_val($this))->get();
            if ($res !== null)
                return $res;
        }
        return $this->goDepth($predicate, $transformation);
    }

    abstract public function goDepth( FunctionValue $predicate
                                    , FunctionValue $transformation);
}

class HTMLNop extends HTML {
    public function render() {
        return "";
    }
    
    public function goDepth( FunctionValue $predicate
                           , FunctionValue $transformation) {
        return null;
    }
}

class HTMLText extends HTML {
    private $_text; // string

    public function text($text = null) {
        if ($text === null) {
            return $this->_text;
        }

        guardIsString($text);
        $this->_text = $text;
    }

    public function __construct($text) {
        guardIsString($text);
        $this->_text = $text;
    }

    public function render() {
        return $this->_text;
    }
    
    public function goDepth( FunctionValue $predicate
                           , FunctionValue $transformation) {
        return null;
    }
}

class HTMLArray extends HTML {
    private $_content; // array of HTML

    public function __construct($content) {
        guardEach($content, "guardIsHTML");
        $this->_content = $content;
    }

    public function content() {
        return $_content;
    }

    public function render() {
        $res = "";
        foreach ($this->_content as $cont) {
            $res .= $cont->render();
        }
        return $res;
    }

    public function concat(HTML $other) {
        if ($other instanceof HTMLArray) {
            $this->_content = array_merge($this->_content, $other->content());
            return $this;
        }
        $this->_content[] = $other;
        return $this;
    }
    
    public function goDepth( FunctionValue $predicate
                           , FunctionValue $transformation) {
        // Code start HERE!!
        foreach ($this->_content as $content) {
            $res = $content->depthFirst($predicate, $transformation);
            if ($res !== null) {
                return $res;
            }  
        }
        return null;
    }
}

class HTMLTag extends HTML {
    private $_name; // string
    private $_attributes; // dict of string => string
    private $_content; // maybe HTML

    public function __construct($name, $attributes, $content) {
        guardIsString($name);
        guardEachAndKeys($attributes, "guardIsString", "guardIsString");
        guardIfNotNull($content, "guardIsHTML");
        $this->_name = $name;
        $this->_attributes = $attributes;
        $this->_content = $content;
    }

    public function render() {
        $head = "<".$this->_name
                   .keysAndValuesToHTMLAttributes($this->_attributes);
        if ($this->_content === null || $this->_content instanceof HTMLNop) {
            return $head."/>";
        }
        return $head.">".$this->_content->render()."</".$this->_name.">";        
    }

    public function name($name = null) {
        if ($name === null) {
            return $this->_name;
        }

        guardIsString($name);
        $this->_name = $name;
    }

    public function attribute($key, $value = null) {
        if ($value === null) {
            return $this->_attributes[$key];
        }

        guardIsString($key);
        guardIsString($value);
        $this->_attributes[$key] = $value;
        return $this;
    }

    public function content($content = 0) {
        if ($content === 0) {
            return $this->_content;
        }

        guardIfNotNull($content, "guardIsHTML");
        $this->_content = $content;
        return $this;
    }
    
    public function goDepth( FunctionValue $predicate
                             , FunctionValue $transformation) {
        return $this->_content->depthFirst($predicate, $transformation);
    }
}

function html_nop() {
    return new HTMLNop();
}
    
function html_tag($name, $attributes, $content = null) {
    return new HTMLTag($name, $attributes, $content);
}

function html_text($content) {
    return new HTMLText($content);
}

function html_concat() {
    return new HTMLArray(func_get_args());
}

?>
