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

declare(strict_types=1);

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test on divider implementation.
 */
class DividerTest extends ILIAS_UI_TestBase
{
    protected function getFactory(): C\Divider\Factory
    {
        return new I\Component\Divider\Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Divider\\Horizontal", $f->horizontal());
    }

    public function testWithLabel(): void
    {
        $f = $this->getFactory();
        $c = $f->horizontal()->withLabel("label");

        $this->assertEquals("label", $c->getLabel());
    }

    public function testRenderHorizontalEmpty(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->horizontal();

        $html = trim($r->render($c));

        $expected_html = "<hr/>";

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function testRenderHorizontalWithLabel(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->horizontal()->withLabel("label");

        $html = trim($r->render($c));
        $expected_html = '<hr class="il-divider-with-label" /><h4 class="il-divider">label</h4>';

        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }

    public function testRenderVertical(): void
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->vertical();

        $html = trim($r->render($c));
        $expected_html = '<span class="glyphicon il-divider-vertical" aria-hidden="true"></span>';

        $this->assertHTMLEquals("<div>" . $expected_html . "</div>", "<div>" . $html . "</div>");
    }
}
