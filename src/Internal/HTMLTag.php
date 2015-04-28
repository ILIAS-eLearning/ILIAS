<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

use Lechimp\Formlets\Internal\Checking as C;

class HTMLTag extends HTML {
    private $_name; // string
    private $_attributes; // dict of string => string
    private $_content; // maybe HTML

    public function __construct($name, $attributes, $content) {
        C::guardIsString($name);
        C::guardEachAndKeys($attributes, "guardIsString", "guardIsString");
        C::guardIfNotNull($content, "guardIsHTML");
        $this->_name = $name;
        $this->_attributes = $attributes;
        $this->_content = $content;
    }

    public function render() {
        $head = "<".$this->_name
                   .self::keysAndValuesToHTMLAttributes($this->_attributes);
        if ($this->_content === null || $this->_content instanceof HTMLNop) {
            return $head."/>";
        }
        return $head.">".$this->_content->render()."</".$this->_name.">";        
    }

    public function name($name = null) {
        if ($name === null) {
            return $this->_name;
        }

        C::guardIsString($name);
        $this->_name = $name;
    }

    public function attribute($key, $value = null) {
        if ($value === null) {
            if (!array_key_exists($key, $this->_attributes))
                return null;
            return $this->_attributes[$key];
        }

        C::guardIsString($key);
        C::guardIsString($value);
        $this->_attributes[$key] = $value;
        return $this;
    }

    public function content($content = 0) {
        if ($content === 0) {
            return $this->_content;
        }

        C::guardIfNotNull($content, "guardIsHTML");
        $this->_content = $content;
        return $this;
    }
    
    public function goDepth( FunctionValue $predicate
                             , FunctionValue $transformation) {
        if ($this->_content !== null) {
            return $this->_content->depthFirst($predicate, $transformation);
        }
        return null;
    }
}


