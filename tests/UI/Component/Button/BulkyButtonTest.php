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

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation as I;

/**
 * Test on button implementation.
 */
class BulkyButtonTest extends ILIAS_UI_TestBase
{
    private I\Component\Button\Factory $button_factory;
    private I\Component\Symbol\Glyph\Glyph $glyph;
    private I\Component\Symbol\Icon\Standard $icon;

    public function setUp(): void
    {
        $this->button_factory = new I\Component\Button\Factory();
        $this->glyph = new I\Component\Symbol\Glyph\Glyph("briefcase", "briefcase");
        $this->icon = new I\Component\Symbol\Icon\Standard("someExample", "Example", "small", false);
    }

    public function test_implements_factory_interface(): void
    {
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Button\\Bulky",
            $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de")
        );
    }

    public function test_construction_icon_type_wrong(): void
    {
        $this->expectException(TypeError::class);

        $f = $this->button_factory;
        /** @var C\Symbol\Symbol $c */
        $c = new stdClass();
        $f->bulky($c, "", "http://www.ilias.de");
    }

    public function test_glyph_or_icon_for_glyph(): void
    {
        $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de");
        $this->assertEquals(
            $this->glyph,
            $b->getIconOrGlyph()
        );
    }

    public function test_glyph_or_icon_for_icon(): void
    {
        $b = $this->button_factory->bulky($this->icon, "label", "http://www.ilias.de");
        $this->assertEquals(
            $this->icon,
            $b->getIconOrGlyph()
        );
    }

    public function test_engaged(): void
    {
        $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de");
        $this->assertFalse($b->isEngaged());
        $this->assertFalse($b->isEngageable());

        $b = $b->withEngagedState(true);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Button\\Bulky",
            $b
        );
        $this->assertTrue($b->isEngaged());
        $this->assertTrue($b->isEngageable());
    }

    public function test_engageable_disengaged(): void
    {
        $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de");
        $this->assertFalse($b->isEngaged());
        $this->assertFalse($b->isEngageable());

        $b = $b->withEngagedState(false);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Button\\Bulky",
            $b
        );
        $this->assertFalse($b->isEngaged());
        $this->assertTrue($b->isEngageable());
    }

    public function test_with_aria_role(): void
    {
        try {
            $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de")
                                      ->withAriaRole(I\Component\Button\Bulky::MENUITEM);
            $this->assertEquals("menuitem", $b->getAriaRole());
        } catch (InvalidArgumentException $e) {
            $this->assertFalse("This should not happen");
        }
    }

    public function test_with_aria_role_incorrect(): void
    {
        try {
            $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de")
                                 ->withAriaRole("loremipsum");
            $this->assertFalse("This should not happen");
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function test_render_with_glyph_in_context(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de");

        $this->assertHTMLEquals(
            $this->getHtmlWithGlyph(),
            $r->render($b)
        );
    }

    public function test_render_with_glyph_in_context_and_engaged(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de")
                                  ->withEngagedState(true);

        $this->assertHTMLEquals(
            $this->getHtmlWithGlyph(true, true),
            $r->render($b)
        );
    }

    public function test_render_with_glyph_in_context_and_disengaged(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->glyph, "label", "http://www.ilias.de")
                                  ->withEngagedState(true);
        $this->assertHTMLEquals(
            $this->getHtmlWithGlyph(true),
            $r->render($b)
        );
    }

    protected function getHtmlWithGlyph(bool $engeagable = false, bool $engeaged = false): string
    {
        $aria_pressed = "";
        $engaged_class = "";
        if ($engeagable) {
            if ($engeagable) {
                $aria_pressed = " aria-pressed='true'";
                $engaged_class = " engaged";
            } else {
                $aria_pressed = " aria-pressed='false'";
                $engaged_class = " disengaged";
            }
        }
        return ''
            . '<button class="btn btn-bulky' . $engaged_class . '" data-action="http://www.ilias.de" id="id_1" ' . $aria_pressed . '>'
            . '	<span class="glyph" role="img">'
            . '		<span class="glyphicon glyphicon-briefcase" aria-hidden="true"></span>'
            . '	</span>'
            . '	<span class="bulky-label">label</span>'
            . '</button>';
    }

    public function test_render_with_icon(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->icon, "label", "http://www.ilias.de");

        $expected = ''
            . '<button class="btn btn-bulky" data-action="http://www.ilias.de" id="id_1">'
            . '	<img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
            . '	<span class="bulky-label">label</span>'
            . '</button>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function test_render_button_with_aria_role_menuitem_not_engageable(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->icon, "label", "http://www.ilias.de")
            ->withAriaRole(I\Component\Button\Bulky::MENUITEM);

        $expected = ''
            . '<button class="btn btn-bulky" data-action="http://www.ilias.de" id="id_1" role="menuitem">'
            . ' <img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
            . '	<span class="bulky-label">label</span>'
            . '</button>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function test_render_button_with_aria_role_menuitem_is_engageable(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->icon, "label", "http://www.ilias.de")
            ->withEngagedState(false)
            ->withAriaRole(I\Component\Button\Bulky::MENUITEM);

        $expected = ''
            . '<button class="btn btn-bulky" data-action="http://www.ilias.de" id="id_1" role="menuitem" aria-haspopup="true">'
            . ' <img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
            . '	<span class="bulky-label">label</span>'
            . '</button>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }

    public function testRenderWithLabelAndAltImageSame(): void
    {
        $r = $this->getDefaultRenderer();
        $b = $this->button_factory->bulky($this->icon, "Example", "http://www.ilias.de")
                                  ->withEngagedState(false)
                                  ->withAriaRole(I\Component\Button\Bulky::MENUITEM);

        $expected = ''
            . '<button class="btn btn-bulky" data-action="http://www.ilias.de" id="id_1" role="menuitem" aria-haspopup="true">'
            . ' <img class="icon someExample small" src="./templates/default/images/icon_default.svg" alt=""/>'
            . '	<span class="bulky-label">Example</span>'
            . '</button>';

        $this->assertHTMLEquals(
            $expected,
            $r->render($b)
        );
    }
}
