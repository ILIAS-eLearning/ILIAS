<?php
require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Checks if the HTML used for the Client tests is rendered as specified
 */
class CounterClientHtmlTest extends ILIAS_UI_TestBase
{
    public function getGlyphFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory();
    }

    public function getCounterFactory()
    {
        return new \ILIAS\UI\Implementation\Component\Counter\Factory();
    }

    public function testRenderClientHtml()
    {
        $counter_factory = $this->getCounterFactory();
        $expected_html = file_get_contents(__DIR__ . "/../../Client/Counter/CounterTest.html");

        $rendered_html = '
            <div>
                <div id="testEmpty">
                </div>
                <div id="test1">
                    RENDERED_HTML_OF_TEST_1
                </div>
            
                <div id="test2">
                    RENDERED_HTML_OF_TEST_2
                </div>
            </div>';


        $add_glpyh = $this->getGlyphFactory()->add("");
        $glyph_with_counter = $add_glpyh->withCounter($counter_factory->status(1))
                                        ->withCounter($counter_factory->novelty(5));

        $r = $this->getDefaultRenderer();
        $rendered_html_of_test_1 = $r->render($glyph_with_counter);
        $rendered_html_of_test_2 = $rendered_html_of_test_1.$rendered_html_of_test_1;

        $rendered_html = str_replace("RENDERED_HTML_OF_TEST_1",$rendered_html_of_test_1,$rendered_html);
        $rendered_html = str_replace("RENDERED_HTML_OF_TEST_2",$rendered_html_of_test_2,$rendered_html);

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected_html), $this->brutallyTrimHTML($rendered_html));
    }
}