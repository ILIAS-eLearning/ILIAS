<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * Representation of html entities. This does not in any way guarantee to 
 * produce valid HTML or something.
 */

require_once("checking.php");
require_once("helpers.php");

abstract class HTML {
    abstract public function render();
    public function concat(HTML $right) {
        return html_concat($this, $right);
    }
}

class HTMLNop extends HTML {
    public function render() {
        return "";
    }
}

class HTMLText extends HTML {
    private $_text; // string

    public function __construct($text) {
        guardIsString($text);
        $this->_text = $text;
    }

    public function render() {
        return $this->_text;
    }
}

class HTMLArray extends HTML {
    private $_content; // array of HTML

    public function __construct($content) {
        guardEach($content, "guardHTML");
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

function html_concat(HTML $left, HTML $right) {
    return html_concatA(array($left, $right));
}

function html_concatA($array) {
    return new HTMLArray($array);
}

?>
