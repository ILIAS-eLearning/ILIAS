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

require_once(__DIR__ . "/../../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Component as C;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\Attributes\Depends;

class PromptStateTest extends \ILIAS_UI_TestBase
{
    protected string $uri_stub_value;
    protected \ILIAS\Data\URI & Stub $uri_stub;
    protected string $prompt_content_stub_html;
    protected C\Prompt\IsPromptContent & Stub $prompt_content_stub;
    protected string $button_stub_html;
    protected I\Button\Standard & Stub $button_stub;
    protected string $form_stub_html;
    protected I\Input\Container\Form\Standard & Stub $form_stub;
    protected string $message_box_stub_html;
    protected I\MessageBox\MessageBox & Stub $message_box_stub;

    protected function setUp(): void
    {
        $this->uri_stub_value = sha1(\ILIAS\Data\URI::class);
        $this->uri_stub = $this->createStub(\ILIAS\Data\URI::class);
        $this->uri_stub->method('__toString')->willReturn($this->uri_stub_value);

        $this->prompt_content_stub_html = sha1(C\Prompt\IsPromptContent::class);
        $this->prompt_content_stub = $this->createStub(C\Prompt\IsPromptContent::class);
        $this->prompt_content_stub->method('getCanonicalName')->willReturn($this->prompt_content_stub_html);

        $this->button_stub_html = sha1(I\Button\Standard::class);
        $this->button_stub = $this->createStub(I\Button\Standard::class);
        $this->button_stub->method('getCanonicalName')->willReturn($this->button_stub_html);

        $this->form_stub_html = sha1(I\Input\Container\Form\Standard::class);
        $this->form_stub = $this->createStub(I\Input\Container\Form\Standard::class);
        $this->form_stub->method('getCanonicalName')->willReturn($this->form_stub_html);

        $this->message_box_stub_html = sha1(I\MessageBox\MessageBox::class);
        $this->message_box_stub = $this->createStub(I\MessageBox\MessageBox::class);
        $this->message_box_stub->method('getCanonicalName')->willReturn($this->message_box_stub_html);

        parent::setUp();
    }

    public function getUIFactory(): \NoUIFactory
    {
        $real_state_factory = new I\Prompt\State\Factory();

        $prompt_factory_stub = $this->createStub(I\Prompt\Factory::class);
        $prompt_factory_stub->method('state')->willReturn($real_state_factory);

        $button_factory_stub = $this->createStub(I\Button\Factory::class);
        $button_factory_stub->method('standard')->willReturn($this->button_stub);

        return new class($prompt_factory_stub, $button_factory_stub) extends \NoUIFactory {
            public function __construct(
                protected I\Prompt\Factory $prompt_factory,
                protected I\Button\Factory $button_factory,
            ) {
            }
            public function button(): I\Button\Factory
            {
                return $this->button_factory;
            }
            public function prompt(): I\Prompt\Factory
            {
                return $this->prompt_factory;
            }
        };
    }

    public function testCloseComposition(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->close();

        $this->assertSame(I\Prompt\State\Instruction::CLOSE, $state->getInstruction());
        $this->assertNull($state->getTitle());
        $this->assertNull($state->getRedirect());
        $this->assertNull($state->getPrimaryContent());
        $this->assertEmpty($state->getSecondaryContent());
    }

    public function testRedirectComposition(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->redirect($this->uri_stub);

        $this->assertSame(I\Prompt\State\Instruction::REDIRECT, $state->getInstruction());
        $this->assertNull($state->getTitle());
        $this->assertSame($state->getRedirect(), $this->uri_stub);
        $this->assertNull($state->getPrimaryContent());
        $this->assertEmpty($state->getSecondaryContent());
    }

    public function testShowCompositionWithPrimaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->prompt_content_stub);

        $this->assertSame(I\Prompt\State\Instruction::SHOW, $state->getInstruction());
        $this->assertNull($state->getTitle());
        $this->assertNull($state->getRedirect());
        $this->assertSame($state->getPrimaryContent(), $this->prompt_content_stub);
        $this->assertEmpty($state->getSecondaryContent());
    }

    #[Depends('testShowCompositionWithPrimaryContent')]
    public function testShowCompositionWithSecondaryContent(): void
    {
        $primary_content = clone $this->prompt_content_stub;
        $secondary_content = clone $this->prompt_content_stub;

        $state = $this->getUIFactory()->prompt()->state()->show($primary_content, $secondary_content);

        $this->assertSame($state->getPrimaryContent(), $primary_content);
        $this->assertSame($state->getSecondaryContent(), [$secondary_content]);
    }

    #[Depends('testCloseComposition')]
    public function testRenderClose(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->close();
        $renderer = $this->getDefaultRenderer();

        $expected_instruction = I\Prompt\State\Instruction::CLOSE->value;
        $expected = <<<HTML
<div class="c-prompt-state" data-instruction="$expected_instruction" data-redirect-url="">
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testRedirectComposition')]
    public function testRenderRedirect(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->redirect($this->uri_stub);
        $renderer = $this->getDefaultRenderer();

        $expected_instruction = I\Prompt\State\Instruction::REDIRECT->value;
        $expected = <<<HTML
<div class="c-prompt-state" data-instruction="$expected_instruction" data-redirect-url="$this->uri_stub_value">
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testShowCompositionWithPrimaryContent')]
    public function testRenderShowWithFormAsPrimaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->form_stub);
        $renderer = $this->getDefaultRenderer(null, [$this->button_stub, $this->form_stub]);

        $expected = <<<HTML
<section data-section="content">
    $this->form_stub_html
</section>
<section data-section="buttons">$this->button_stub_html</section>
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testRenderShowWithFormAsPrimaryContent')]
    public function testRenderShowWithFormAndAdditionalSubmitButtonsAsPrimaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->form_stub);
        $renderer = $this->getDefaultRenderer(null, [$this->button_stub, $this->form_stub]);

        $this->form_stub->method('getAdditionalFormActions')->willReturn(['action' => 'label']);

        $expected_button_html = str_repeat($this->button_stub_html, 2);
        $expected = <<<HTML
<section data-section="content">
    $this->form_stub_html
</section>
<section data-section="buttons">$expected_button_html</section>
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testShowCompositionWithPrimaryContent')]
    public function testRenderShowWithMessageBoxAsPrimaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->message_box_stub);
        $renderer = $this->getDefaultRenderer(null, [$this->message_box_stub]);

        $expected_instruction = I\Prompt\State\Instruction::SHOW->value;
        $expected = <<<HTML
<section data-section="content">
    $this->message_box_stub_html
</section>
<section data-section="buttons"></section>
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testRenderShowWithMessageBoxAsPrimaryContent')]
    public function testRenderShowWithMessageBoxAndButtonsAsPrimaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->message_box_stub);
        $renderer = $this->getDefaultRenderer(null, [$this->button_stub, $this->message_box_stub]);

        $this->message_box_stub->method('getButtons')->willReturn([$this->button_stub]);

        $expected_instruction = I\Prompt\State\Instruction::SHOW->value;
        $expected = <<<HTML
<section data-section="content">
    $this->message_box_stub_html
</section>
<section data-section="buttons">$this->button_stub_html</section>
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testShowCompositionWithSecondaryContent')]
    public function testRenderShowWithMessageBoxAsSecondaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->prompt_content_stub, $this->message_box_stub);
        $renderer = $this->getDefaultRenderer(null, [$this->prompt_content_stub, $this->message_box_stub]);

        $expected = <<<HTML
<section data-section="content">
    $this->message_box_stub_html
    $this->prompt_content_stub_html
</section>
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }

    #[Depends('testShowCompositionWithSecondaryContent')]
    public function testRenderShowWithArbitrarySecondaryContent(): void
    {
        $state = $this->getUIFactory()->prompt()->state()->show($this->form_stub, $this->prompt_content_stub);
        $renderer = $this->getDefaultRenderer(null, [
            $this->button_stub,
            $this->form_stub,
            $this->prompt_content_stub,
        ]);

        $expected = <<<HTML
<section data-section="content">
    $this->form_stub_html
    $this->prompt_content_stub_html
</section>
HTML;

        $actual = $renderer->render($state);

        $this->assertStringContainsString($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($actual));
    }
}
