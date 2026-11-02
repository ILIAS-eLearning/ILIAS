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

require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\Attributes\Depends;

class PromptTest extends \ILIAS_UI_TestBase
{
    protected string $uri_stub_value;
    protected \ILIAS\Data\URI & Stub $uri_stub;
    protected string $message_box_stub_html;
    protected I\MessageBox\MessageBox & Stub $message_box_stub;

    protected function setUp(): void
    {
        $this->uri_stub_value = sha1(\ILIAS\Data\URI::class);
        $this->uri_stub = $this->createStub(\ILIAS\Data\URI::class);
        $this->uri_stub->method('__toString')->willReturn($this->uri_stub_value);

        $this->message_box_stub_html = sha1(I\MessageBox\MessageBox::class);
        $this->message_box_stub = $this->createStub(I\MessageBox\MessageBox::class);
        $this->message_box_stub->method('getCanonicalName')->willReturn($this->message_box_stub_html);

        parent::setUp();
    }

    public function getUIFactory(): \NoUIFactory
    {
        $real_prompt_factory = new I\Prompt\Factory(
            $this->createStub(I\SignalGeneratorInterface::class),
            $this->createStub(I\Prompt\State\Factory::class),
        );

        $message_box_factory_stub = $this->createStub(I\MessageBox\Factory::class);
        $message_box_factory_stub->method('failure')->willReturn($this->message_box_stub);

        return new class($real_prompt_factory, $message_box_factory_stub) extends \NoUIFactory {
            public function __construct(
                protected I\Prompt\Factory $prompt_factory,
                protected I\MessageBox\Factory $message_box_factory,
            ) {
            }
            public function messageBox(): I\MessageBox\Factory
            {
                return $this->message_box_factory;
            }
            public function prompt(): I\Prompt\Factory
            {
                return $this->prompt_factory;
            }
        };
    }

    public function testRender(): void
    {
        $prompt = $this->getUIFactory()->prompt()->standard($this->uri_stub);

        $renderer = $this->getDefaultRenderer(null, [$this->message_box_stub]);

        $expected = <<<HTML
<div class="c-prompt" id="id_1">
    <dialog aria-labelledby="id_1_title">
        <div class="c-prompt__header">
            <form class="c-prompt__close">
                <button formmethod="dialog" class="close" aria-label="close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </form>
            <span class="c-prompt__title" id="id_1_title"></span>
        </div>
        <hr>
        <div class="c-prompt__contents"></div>
        <hr>
        <div class="c-prompt__buttons">
            <form class="c-prompt__close">
                <button formmethod="dialog" class="btn btn-default" >close</button>
            </form>
        </div>
        <section class="c-prompt__scripts"></section>
    </dialog>
    <template>$this->message_box_stub_html</template>
</div>
HTML;

        $actual = $renderer->render($prompt);

        $this->assertEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    public function testRenderWithTitle(): void
    {
        $prompt = $this->getUIFactory()->prompt()->standard($this->uri_stub);

        $title = sha1('Hello World!');
        $prompt = $prompt->withTitle($title);

        $renderer = $this->getDefaultRenderer(null, [$this->message_box_stub]);

        $expected = <<<HTML
<span class="c-prompt__title" id="id_1_title">$title</span>
HTML;

        $actual = $renderer->render($prompt);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }
}
