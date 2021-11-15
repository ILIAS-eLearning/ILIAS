<?php declare(strict_types=1);

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test on divider implementation.
 */
class DividerTest extends ILIAS_UI_TestBase
{
    protected function getFactory() : C\Divider\Factory
    {
        return new I\Component\Divider\Factory();
    }

    public function test_implements_factory_interface() : void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Divider\\Horizontal", $f->horizontal());
    }

    public function test_with_label() : void
    {
        $f = $this->getFactory();
        $c = $f->horizontal()->withLabel("label");

        $this->assertEquals("label", $c->getLabel());
    }

    public function test_render_horizontal_empty() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->horizontal();

        $html = trim($r->render($c));

        $expected_html = "<hr/>";

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_horizontal_with_label() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->horizontal()->withLabel("label");

        $html = trim($r->render($c));
        $expected_html = '<hr class="il-divider-with-label" /><h4 class="il-divider">label</h4>';

        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }

    public function test_render_vertical() : void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->vertical();

        $html = trim($r->render($c));
        $expected_html = '<span class="glyphicon il-divider-vertical" aria-hidden="true"></span>';

        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }
}
