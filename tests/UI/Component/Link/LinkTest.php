<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;

/**
 * Test on link implementation.
 */
class LinkTest extends ILIAS_UI_TestBase
{
    public function getLinkFactory() : I\Link\Factory
    {
        return new I\Link\Factory();
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getLinkFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Link\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Link\\Standard",
            $f->standard("label", "http://www.ilias.de")
        );
    }

    public function test_get_label() : void
    {
        $f = $this->getLinkFactory();
        $c = $f->standard("label", "http://www.ilias.de");

        $this->assertEquals("label", $c->getLabel());
    }

    public function test_get_action() : void
    {
        $f = $this->getLinkFactory();
        $c = $f->standard("label", "http://www.ilias.de");

        $this->assertEquals("http://www.ilias.de", $c->getAction());
    }

    public function test_render_link() : void
    {
        $f = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard("label", "http://www.ilias.de");

        $html = $r->render($c);

        $expected_html =
            '<a href="http://www.ilias.de">label</a>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_with_new_viewport() : void
    {
        $f = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard("label", "http://www.ilias.de")->withOpenInNewViewport(true);

        $html = $r->render($c);

        $expected_html =
            '<a href="http://www.ilias.de" target="_blank" rel="noopener">label</a>';

        $this->assertHTMLEquals($expected_html, $html);
    }
}
