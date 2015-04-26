<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 */

use Lechimp\Formlets\Internal\HTML as H;

class HTMLRenderingTest extends PHPUnit_Framework_TestCase {
    /**
    * @dataProvider html_and_results 
    */
    public function testRendersAsExpected($html, $result) {
        $this->assertEquals($html->render(), $result);
    } 

    function html_and_results() {
        return array
            ( array(H::text("foo"), "foo")
            , array(H::nop(), "")
            );
    }    

    /**
     * Test weather '"' in input values gets rendered correctly. 
     */
    function testRendersQuotesCorrectly() {
        $html = H::tag("span", array( "foo" => "\"bar\""));
        $this->assertEquals($html->render(), '<span foo="&quot;bar&quot;"/>');
    }
}

?>
