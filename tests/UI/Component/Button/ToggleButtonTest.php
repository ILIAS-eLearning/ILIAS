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

use ILIAS\UI\Implementation\Component\Signal;
use ILIAS\UI\Implementation\Component\Button\Factory;
use ILIAS\UI\Component\Button\Toggle;

/**
 * Test Toggle Button
 */
class ToggleButtonTest extends ILIAS_UI_TestBase
{
    public function getFactory(): \ILIAS\UI\Implementation\Component\Button\Factory
    {
        return new Factory();
    }

    public function testImplementsFactoryInterface(): void
    {
        $f = $this->getFactory();

        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Button\\Toggle",
            $f->toggle("label", "action_on_string", "action_off_string")
        );
    }

    public function testConstructionActionOnTypeWrong(): void
    {
        $f = $this->getFactory();
        try {
            $f->toggle("label", 1, "action_off_string");
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testConstructionActionOffTypeWrong(): void
    {
        $f = $this->getFactory();
        try {
            $f->toggle("label", "action_on_string", 2);
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSetOnOnDefault(): void
    {
        $f = $this->getFactory();
        $button = $f->toggle("label", "action_on_string", "action_off_string", true);

        $this->assertTrue($button->isEngaged());
    }

    public function testAppendOnAction(): void
    {
        $f = $this->getFactory();
        $signal_on1 = $this->createMock(Signal::class);
        $signal_on2 = $this->createMock(Signal::class);
        $signal_off = $this->createMock(Signal::class);
        $button = $f->toggle("label", $signal_on1, $signal_off);
        $this->assertEquals([$signal_on1], $button->getActionOn());

        $button = $button->withAdditionalToggleOnSignal($signal_on2);
        $this->assertEquals([$signal_on1, $signal_on2], $button->getActionOn());
    }

    public function testAppendOffAction(): void
    {
        $f = $this->getFactory();
        $signal_off1 = $this->createMock(Signal::class);
        $signal_off2 = $this->createMock(Signal::class);
        $button = $f->toggle("label", "action_on", $signal_off1);
        $this->assertEquals([$signal_off1], $button->getActionOff());

        $button = $button->withAdditionalToggleOffSignal($signal_off2);
        $this->assertEquals([$signal_off1, $signal_off2], $button->getActionOff());
    }

    public function testRenderWithLabel(): void
    {
        $r = $this->getDefaultRenderer();
        $button = $this->getFactory()->toggle("label", "action_on_string", "action_off_string");

        $expected = <<<EOT
		<label>label</label>
<button class="il-toggle-button off" id="id_1" aria-pressed="false">
    <span class="il-toggle-label-on">toggle_on</span>
    <span class="il-toggle-label-off">toggle_off</span>
    <span class="il-toggle-switch"></span>
</button>
EOT;

        $this->assertHTMLEquals("<div>" . $expected . "</div>", "<div>" . $r->render($button) . "</div>");
    }

    public function testRenderSetOnOnDefault(): Toggle
    {
        $r = $this->getDefaultRenderer();
        $button = $this->getFactory()->toggle("", "action_on_string", "action_off_string", true);

        $expected = ''
            . '<button class="il-toggle-button on" id="id_1" aria-pressed="false">'    //aria-pressed is set to "true" by JS
            . '    <span class="il-toggle-label-on">toggle_on</span>'
            . '    <span class="il-toggle-label-off">toggle_off</span>'
            . '    <span class="il-toggle-switch"></span>'
            . '</button>';

        $this->assertHTMLEquals($expected, $r->render($button));
        return $button;
    }

    public function testRenderWithSignals(): void
    {
        $r = $this->getDefaultRenderer();
        $signal_on = $this->createMock(Signal::class);
        $signal_on->method("__toString")
            ->willReturn("MOCK_SIGNAL");
        $signal_off = $this->createMock(Signal::class);
        $signal_off->method("__toString")
            ->willReturn("MOCK_SIGNAL");
        $button = $this->getFactory()->toggle("label", $signal_on, $signal_off);

        $expected = <<<EOT
		<label>label</label>
<button class="il-toggle-button off" id="id_1" aria-pressed="false">
    <span class="il-toggle-label-on">toggle_on</span>
    <span class="il-toggle-label-off">toggle_off</span>
    <span class="il-toggle-switch"></span>
</button>
EOT;

        $this->assertHTMLEquals("<div>" . $expected . "</div>", "<div>" . $r->render($button) . "</div>");
    }

    /**
     * @depends testRenderSetOnOnDefault
     */
    public function testAppendUnavailAction(Toggle $button): void
    {
        $r = $this->getDefaultRenderer();
        $button = $button->withUnavailableAction();

        $html = $r->render($button);

        $expected = ''
            . '<button class="il-toggle-button unavailable" aria-pressed="false" disabled="disabled">'
            . '    <span class="il-toggle-switch"></span>'
            . '</button>';

        $this->assertHTMLEquals(
            $expected,
            $html
        );
    }
}
