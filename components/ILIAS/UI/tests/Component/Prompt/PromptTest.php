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
    protected string $url_builder_stub_value;
    protected \ILIAS\UI\URLBuilder & Stub $url_builder_stub;
    protected string $message_box_stub_html;
    protected I\MessageBox\MessageBox & Stub $message_box_stub;

    protected function setUp(): void
    {
        $this->url_builder_stub_value = sha1(\ILIAS\UI\URLBuilder::class);
        $uri_stub = $this->createStub(\ILIAS\Data\URI::class);
        $uri_stub->method('__toString')->willReturn($this->url_builder_stub_value);
        $this->url_builder_stub = $this->createStub(\ILIAS\UI\URLBuilder::class);
        $this->url_builder_stub->method('buildURI')->willReturn($uri_stub);

        $this->message_box_stub_html = sha1(I\MessageBox\MessageBox::class);
        $this->message_box_stub = $this->createStub(I\MessageBox\MessageBox::class);
        $this->message_box_stub->method('getCanonicalName')->willReturn($this->message_box_stub_html);

        parent::setUp();
    }

    public function getUIFactory(): \NoUIFactory
    {
        $real_prompt_factory = new I\Prompt\Factory(
            new I\SignalGenerator(),
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
        $prompt = $this->getUIFactory()->prompt()->standard($this->url_builder_stub);

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
        $prompt = $this->getUIFactory()->prompt()->standard($this->url_builder_stub);

        $title = sha1('Hello World!');
        $prompt = $prompt->withTitle($title);

        $renderer = $this->getDefaultRenderer(null, [$this->message_box_stub]);

        $expected = <<<HTML
<span class="c-prompt__title" id="id_1_title">$title</span>
HTML;

        $actual = $renderer->render($prompt);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    public function testShowSignal(): void
    {
        $prompt = $this->getUIFactory()->prompt()->standard($this->url_builder_stub);
        $signal = $prompt->getShowSignal();
        $this->assertSame(['url' => $this->url_builder_stub_value], $signal->getOptions());
    }

    public function testShowSignalAfterWithParameter(): void
    {
        $test_value = 'some_new_parameter_value';
        $url_builder_token_stub = $this->createStub(\ILIAS\UI\URLBuilderToken::class);

        $initial_uri_stub = $this->createStub(\ILIAS\Data\URI::class);
        $initial_uri_stub->method('__toString')->willReturn('initial_uri');
        $altered_uri_stub = $this->createStub(\ILIAS\Data\URI::class);
        $altered_uri_stub->method('__toString')->willReturn('altered_uri');

        $url_builder_mock = $this->createMock(\ILIAS\UI\URLBuilder::class);
        $url_builder_mock->method('buildURI')->willReturn($initial_uri_stub, $altered_uri_stub);

        $prompt = $this->getUIFactory()->prompt()->standard($url_builder_mock);

        $initial_signal = $prompt->getShowSignal();
        $this->assertSame(['url' => $initial_uri_stub->__toString()], $initial_signal->getOptions());

        $url_builder_mock
            ->expects($this->once())
            ->method('withParameter')
            ->with($url_builder_token_stub, $test_value)
            ->willReturnSelf();
        $prompt = $prompt->withParameter($url_builder_token_stub, $test_value);

        $altered_signal = $prompt->getShowSignal();
        $this->assertSame(['url' => $altered_uri_stub->__toString()], $altered_signal->getOptions());
    }

    public function testShowSignalWithLegacyParameter(): void
    {
        $prompt = $this->getUIFactory()->prompt()->standard($this->url_builder_stub);
        $this->expectException(\LogicException::class);
        $prompt->getShowSignal($this->createStub(\ILIAS\Data\URI::class));
    }
}
