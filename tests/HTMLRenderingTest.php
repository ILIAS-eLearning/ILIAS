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

class HTMLRenderingTest extends PHPUnit_Framework_TestCase {
    /**
    * @dataProvider html_and_results 
    */
    public function testRendersAsExpected($html, $result) {
        $this->assertEquals($html->render(), $result);
    } 

    function html_and_results() {
        return array
            ( array(html_text("foo"), "foo")
            , array(html_nop(), "")
            );
    }    

    /**
     * Test weather '"' in input values gets rendered correctly. 
     */
    function testRendersQuotesCorrectly() {
        $html = html_tag("span", array( "foo" => "\"bar\""));
        $this->assertEquals($html->render(), '<span foo="&quot;bar&quot;"/>');
    }
}

?>
