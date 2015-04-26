<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

namespace Lechimp\Formlets\Internal;

/*
 * Representation of html entities. This does not in any way guarantee to 
 * produce valid HTML or something.
 */

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

    function nop() {
        return new HTMLNop();
    }
        
    function tag($name, $attributes, $content = null) {
        return new HTMLTag($name, $attributes, $content);
    }

    function text($content) {
        return new HTMLText($content);
    }

    function concat() {
        return new HTMLArray(func_get_args());
    }

    function harray($array) {
        return new HTMLArray($array);
    }


    function keysAndValuesToHTMLAttributes($attributes) {
        $str = "";
        foreach ($attributes as $key => $value) {
            guardIsString($key);
            if ($value !== null)
                guardIsString($value);
            $value = str_replace('"', '&quot;', $value);
            $str .= " ".$key.($value !== null ? "=\"$value\"" : "");
        } 
        return $str;
    }
}

?>
