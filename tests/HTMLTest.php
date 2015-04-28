<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\Values as V;
use Lechimp\Formlets\Internal\HTML as H;
use Lechimp\Formlets\Internal\HTMLText;

class HTMLTest extends PHPUnit_Framework_TestCase {
    /**
    * @dataProvider depth_first_searches 
    */
    public function testDepthFirstSearch($html, $predicate, $transformation, $result) {
        $this->assertEquals($html->depthFirst($predicate, $transformation), $result);
    } 

    /**
     * Check if depth first finds all nodes matching a predicate in the expected
       order.
     */
    public function testDepthFirstFindsAll() {
        $html = H::tag("foo", array(), H::concat(
                    H::tag("bar", array(), H::concat(
                        H::text("a"),
                        H::tag("blaw", array(), H::concat(
                            H::text("b"),
                            H::text("c")
                        )),
                        H::text("d")
                    )),
                    H::text("e"),
                    H::tag("baz", array(), H::concat(
                        H::text("f"),
                        H::text("g")
                    )),
                    H::text("h")
                ));
        $is_text = V::fn(function ($html) {
            return $html instanceof HTMLText;
        });
        $str = "";
        $collect_str = V::fn(function ($html) use (&$str) {
            $str .= $html->content();
        });
        $html->depthFirst($is_text, $collect_str);
        $this->assertEquals($str, "abcdefgh");
    }

    function depth_first_searches() {
        $is_text = V::fn(function ($html) {
            return $html instanceof HTMLText;
        });
        $get_text = V::fn(function($html) {
            return $html->content();
        });
        return array
            ( array( H::text("Hello World")
                   , $is_text
                   , $get_text
                   , "Hello World"
                   )
            , array( H::nop()
                   , $is_text
                   , $get_text
                   , null 
                   )
            , array( H::tag("foo", array(), H::text("Hello World"))
                   , $is_text
                   , $get_text
                   , "Hello World"
                   )
            , array( H::tag("foo", array(), H::concat(
                        H::text("Hello World"),
                        H::text("Hello World")
                     ))
                   , $is_text
                   , $get_text
                   , "Hello World"
                   )
            , array( H::tag("foo", array(), H::concat(
                        H::tag("bar", array(), H::concat(
                            H::text("Hello World"),
                            H::text("Blub")
                        )),
                        H::text("Blaw")
                     ))
                   , $is_text
                   , $get_text
                   , "Hello World"
                   )
            );
    } 
}

