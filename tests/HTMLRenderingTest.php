<?php

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
