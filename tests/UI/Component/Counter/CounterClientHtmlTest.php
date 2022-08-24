<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;

/**
 * Checks if the HTML used for the Client tests is rendered as specified
 */
class CounterClientHtmlTest extends ILIAS_UI_TestBase
{
    public function getGlyphFactory(): \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory
    {
        return new \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory();
    }

    public function getCounterFactory(): \ILIAS\UI\Implementation\Component\Counter\Factory
    {
        return new \ILIAS\UI\Implementation\Component\Counter\Factory();
    }

    public function testRenderClientHtml(): void
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
        $rendered_html_of_test_2 = $rendered_html_of_test_1 . $rendered_html_of_test_1;

        $rendered_html = str_replace("RENDERED_HTML_OF_TEST_1", $rendered_html_of_test_1, $rendered_html);
        $rendered_html = str_replace("RENDERED_HTML_OF_TEST_2", $rendered_html_of_test_2, $rendered_html);

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected_html), $this->brutallyTrimHTML($rendered_html));
    }
}
