<?php
/******************************************************************************
 * An implementation of the "Formlets"-abstraction in PHP.
 * Copyright (c) 2014, 2015 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 + This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once("formlets/html.php");

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
        $html = html_tag("foo", array(), html_concat(
                    html_tag("bar", array(), html_concat(
                        html_text("a"),
                        html_tag("blaw", array(), html_concat(
                            html_text("b"),
                            html_text("c")
                        )),
                        html_text("d")
                    )),
                    html_text("e"),
                    html_tag("baz", array(), html_concat(
                        html_text("f"),
                        html_text("g")
                    )),
                    html_text("h")
                ));
        $is_html_text = _fn(function ($html) {
            return $html instanceof HTMLText;
        });
        $str = "";
        $collect_str = _fn(function ($html) use (&$str) {
            $str .= $html->text();
        });
        $html->depthFirst($is_html_text, $collect_str);
        $this->assertEquals($str, "abcdefgh");
    }

    function depth_first_searches() {
        $is_html_text = _fn(function ($html) {
            return $html instanceof HTMLText;
        });
        $get_text = _fn(function($html) {
            return $html->text();
        });
        return array
            ( array( html_text("Hello World")
                   , $is_html_text
                   , $get_text
                   , "Hello World"
                   )
            , array( html_nop()
                   , $is_html_text
                   , $get_text
                   , null 
                   )
            , array( html_tag("foo", array(), html_text("Hello World"))
                   , $is_html_text
                   , $get_text
                   , "Hello World"
                   )
            , array( html_tag("foo", array(), html_concat(
                        html_text("Hello World"),
                        html_text("Hello World")
                     ))
                   , $is_html_text
                   , $get_text
                   , "Hello World"
                   )
            , array( html_tag("foo", array(), html_concat(
                        html_tag("bar", array(), html_concat(
                            html_text("Hello World"),
                            html_text("Blub")
                        )),
                        html_text("Blaw")
                     ))
                   , $is_html_text
                   , $get_text
                   , "Hello World"
                   )
            );
    } 
}

