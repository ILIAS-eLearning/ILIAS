<?php

declare(strict_types=1);

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

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Component\Link as C;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data;

/**
 * Testing behavior of the Bulky Link.
 */
class BulkyLinkTest extends ILIAS_UI_TestBase
{
    protected I\Link\Factory $factory;
    protected I\Symbol\Glyph\Glyph $glyph;
    protected I\Symbol\Icon\Standard $icon;
    protected Data\URI $target;

    public function setUp(): void
    {
        $this->factory = new I\Link\Factory();
        $this->glyph = new I\Symbol\Glyph\Glyph("briefcase", "briefcase");
        $this->icon = new I\Symbol\Icon\Standard("someExample", "Example", "small", false);
        $this->target = new Data\URI("http://www.ilias.de");
    }

    public function testImplementsInterfaces(): void
    {
        $link = $this->factory->bulky($this->glyph, "label", $this->target);
        $this->assertInstanceOf(C\Bulky::class, $link);
        $this->assertInstanceOf(C\Link::class, $link);
    }

    public function testWrongConstruction(): void
    {
        $this->expectException(\TypeError::class);
        $this->factory->bulky('wrong param', "label", $this->target);
    }

    public function testWithAriaRole(): void
    {
        try {
            $b = $this->factory->bulky($this->glyph, "label", $this->target)
            ->withAriaRole(I\Button\Bulky::MENUITEM);
            $this->assertEquals("menuitem", $b->getAriaRole());
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen");
        }
    }

    public function testWithAriaRoleIncorrect(): void
    {
        try {
            $this->factory->bulky($this->glyph, "label", $this->target)
            ->withAriaRole("loremipsum");
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetLabell(): void
    {
        $label = 'some label for the link';
        $link = $this->factory->bulky($this->glyph, $label, $this->target);
        $this->assertEquals($label, $link->getLabel());
    }

    public function testGetGlyphSymbol(): void
    {
        $link = $this->factory->bulky($this->glyph, "label", $this->target);
        $this->assertEquals($this->glyph, $link->getSymbol());
        $link = $this->factory->bulky($this->icon, "label", $this->target);
        $this->assertEquals($this->icon, $link->getSymbol());
    }

    public function testGetAction(): void
    {
        $plain = "http://www.ilias.de";
        $with_query = $plain . "?query1=1";
        $with_multi_query = $with_query . "&query2=2";
        $with_fragment = $plain . "#fragment";
        $with_multi_query_and_fragment_uri = $with_multi_query . $with_fragment;

        $plain_uri = new Data\URI($plain);
        $with_query_uri = new Data\URI($with_query);
        $with_multi_query_uri = new Data\URI($with_multi_query);
        $with_fragment_uri = new Data\URI($with_fragment);
        $with_multi_query_and_fragment_uri = new Data\URI($with_multi_query_and_fragment_uri);

        $this->assertEquals($plain, $this->factory->bulky($this->glyph, "label", $plain_uri)->getAction());
        $this->assertEquals($with_query, $this->factory->bulky($this->glyph, "label", $with_query_uri)->getAction());
        $this->assertEquals($with_multi_query, $this->factory->bulky($this->glyph, "label", $with_multi_query_uri)->getAction());
        $this->assertEquals($with_fragment_uri, $this->factory->bulky($this->glyph, "label", $with_fragment_uri)->getAction());
        $this->assertEquals($with_multi_query_and_fragment_uri, $this->factory->bulky($this->glyph, "label", $with_multi_query_and_fragment_uri)->getAction());
    }

    public function testRenderingGlyph(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->glyph, "label", $this->target);

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de">'
            . '	<span class="glyph" role="img">'
            . '		<span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span>'
            . '	</span>'
            . '	<span class="bulky-label">label</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function testRenderingIcon(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "label", $this->target);

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de">'
            . '	<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
            . '	<span class="bulky-label">label</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }
    public function testRenderingWithId(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "label", $this->target)
            ->withAdditionalOnloadCode(function ($id) {
                return '';
            });

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de" id="id_1">'
            . '<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
            . ' <span class="bulky-label">label</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function testRenderWithAriaRoleMenuitem(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "label", $this->target)
        ->withAriaRole(I\Button\Bulky::MENUITEM);

        $expected = ''
        . '<a class="il-link link-bulky" href="http://www.ilias.de" role="menuitem">'
        . '<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
        . ' <span class="bulky-label">label</span>'
        . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function testRenderWithLabelAndAltImageSame(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->factory->bulky($this->icon, "Example", $this->target)
                           ->withAriaRole(I\Button\Bulky::MENUITEM);

        $expected = ''
            . '<a class="il-link link-bulky" href="http://www.ilias.de" role="menuitem">'
            . '<img class="icon someExample small" src="./templates/default/images/icon_default.svg"  alt=""/>'
            . ' <span class="bulky-label">Example</span>'
            . '</a>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }
}
