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

namespace Component\Prompt\State;

require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use NoUIFactory;

class StateTest extends \ILIAS_UI_TestBase
{
    protected I\Button\Standard $close_button;
    protected string $close_button_html;
    protected I\Button\Factory $button_factory;

    public function setUp(): void
    {
        $this->close_button = $this->createStub(I\Button\Standard::class);
        $this->close_button_html = sha1(I\Button\Standard::class);
        $this->close_button->method('getCanonicalName')->willReturn($this->close_button_html);
        $this->close_button->method('withOnLoadCode')->willReturn($this->close_button);

        $this->button_factory = $this->createStub(I\Button\Factory::class);
        $this->button_factory->method('standard')->willReturn($this->close_button);

        parent::setUp();
    }

    public function getUIFactory(): NoUIFactory
    {
        return new class ($this->button_factory) extends NoUIFactory {
            public function __construct(
                protected I\Button\Factory $button_factory
            ) {
            }

            public function button(): I\Button\Factory
            {
                return $this->button_factory;
            }
        };
    }

    public function testWithAdditionalSecondaryContent(): void
    {
        $content_1 = $this->createStub(C\Prompt\IsPromptContent::class);
        $content_2 = $this->createStub(C\Prompt\IsPromptContent::class);

        $state = new I\Prompt\State\State(null, $content_1);
        $state = $state->withAdditionalSecondaryContent($content_2);

        $this->assertSame($state->getSecondaryContent(), [$content_1, $content_2]);
    }

    public function testRenderGenericSecondaryContentBelowPrimaryContent(): void
    {
        $primary = $this->createStub(C\Prompt\IsPromptContent::class);
        $primary_html = sha1(C\Prompt\IsPromptContent::class);
        $primary->method('getCanonicalName')->willReturn($primary_html);
        $secondary = $this->createStub(C\Prompt\IsPromptContent::class);
        $secondary_html = sha1(C\MessageBox\MessageBox::class);
        $secondary->method('getCanonicalName')->willReturn($secondary_html);

        $r = $this->getDefaultRenderer(null, [$primary, $secondary, $this->close_button]);

        $state = new I\Prompt\State\State($primary, $secondary);
        $actual_html = $r->render($state);

        $expected_html = <<<EOT
<div class="il-prompt-state">
    <section data-section="il-prompt-state__title"></section>
    <section data-section="il-prompt-state__contents">
        $primary_html
        $secondary_html
    </section>
    <section data-section="il-prompt-state__buttons">
        $this->close_button_html
    </section>
    <section data-section="il-prompt-state__command">
        show
    </section>
    <section data-section="il-prompt-state__parameters"></section>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }

    public function testRenderSecondaryMessageBoxAbovePrimaryContent(): void
    {
        $primary = $this->createStub(C\Prompt\IsPromptContent::class);
        $primary_html = sha1(C\Prompt\IsPromptContent::class);
        $primary->method('getCanonicalName')->willReturn($primary_html);
        $message_box = $this->createStub(C\MessageBox\MessageBox::class);
        $message_box_html = sha1(C\MessageBox\MessageBox::class);
        $message_box->method('getCanonicalName')->willReturn($message_box_html);

        $r = $this->getDefaultRenderer(null, [$primary, $message_box, $this->close_button]);

        $state = new I\Prompt\State\State($primary, $message_box);
        $actual_html = $r->render($state);

        $expected_html = <<<EOT
<div class="il-prompt-state">
    <section data-section="il-prompt-state__title"></section>
    <section data-section="il-prompt-state__contents">
        $message_box_html
        $primary_html
    </section>
    <section data-section="il-prompt-state__buttons">
        $this->close_button_html
    </section>
    <section data-section="il-prompt-state__command">
        show
    </section>
    <section data-section="il-prompt-state__parameters"></section>
</div>
EOT;

        $this->assertEquals(
            $this->brutallyTrimHTML($expected_html),
            $this->brutallyTrimHTML($actual_html),
        );
    }
}
