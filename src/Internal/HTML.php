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
use Lechimp\Formlets\Internal\Values as V;

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
    public function cat(HTML $right) {
        return HTML::concat($this, $right);
    }

    /**
     * Make a depth first search.
     * Performs $transformation on every node that matches $predicate. If 
     * $transformation returns null, search will go on, if it returns a 
     * value, search will stop and return the value.
     */
    public function depthFirst( FunctionValue $predicate
                              , FunctionValue $transformation) {
        C::guardHasArity($predicate, 1); 
        C::guardHasArity($transformation, 1); 
        if ($predicate->apply(V::val($this))->get()) {
            $res = $transformation->apply(V::val($this))->get();
            if ($res !== null)
                return $res;
        }
        return $this->goDepth($predicate, $transformation);
    }

    abstract public function goDepth( FunctionValue $predicate
                                    , FunctionValue $transformation);

    static function nop() {
        return new HTMLNop();
    }
        
    static function tag($name, $attributes, $content = null) {
        return new HTMLTag($name, $attributes, $content);
    }

    static function text($content) {
        return new HTMLText($content);
    }

    static function concat() {
        return new HTMLArray(func_get_args());
    }

    static function harray($array) {
        return new HTMLArray($array);
    }


    static function keysAndValuesToHTMLAttributes($attributes) {
        $str = "";
        foreach ($attributes as $key => $value) {
            C::guardIsString($key);
            if ($value !== null)
                C::guardIsString($value);
            $value = str_replace('"', '&quot;', $value);
            $str .= " ".$key.($value !== null ? "=\"$value\"" : "");
        } 
        return $str;
    }
}

?>
