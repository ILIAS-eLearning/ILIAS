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

use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Implementation\Render\RendererFactory;
use ILIAS\UI\Implementation\Render\FSLoader;
use ILIAS\UI\Implementation\Component\Button\Button;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon;
use ILIAS\UI\Implementation\Component\Input\Field\FormInput;
use ILIAS\UI\Implementation\Component\MessageBox\MessageBox;
use ILIAS\UI\Implementation\Component\Input\Container\Form\Form;
use ILIAS\UI\Implementation\Component\Menu\Menu;
use ILIAS\UI\Component\Component;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class FSLoaderTest extends TestCase
{
    protected RendererFactory & MockObject $default_renderer_factory;
    protected RendererFactory & MockObject $button_renderer_factory;
    protected RendererFactory & MockObject $field_renderer_factory;
    protected RendererFactory & MockObject $message_box_renderer_factory;
    protected RendererFactory & MockObject $form_renderer_factory;
    protected RendererFactory & MockObject $menu_renderer_factory;

    protected FSLoader $fs_loader;

    protected function setUp(): void
    {
        $this->default_renderer_factory = $this->createMock(RendererFactory::class);
        $this->button_renderer_factory = $this->createMock(RendererFactory::class);
        $this->field_renderer_factory = $this->createMock(RendererFactory::class);
        $this->message_box_renderer_factory = $this->createMock(RendererFactory::class);
        $this->form_renderer_factory = $this->createMock(RendererFactory::class);
        $this->menu_renderer_factory = $this->createMock(RendererFactory::class);

        $this->fs_loader = new FSLoader(
            $this->default_renderer_factory,
            $this->button_renderer_factory,
            $this->field_renderer_factory,
            $this->message_box_renderer_factory,
            $this->form_renderer_factory,
            $this->menu_renderer_factory,
        );

        parent::setUp();
    }

    public function testGetRendererUsesRendererFactory(): void
    {
        $loader = $this->getMockBuilder(ILIAS\UI\Implementation\Render\FSLoader::class)
            ->onlyMethods(["getRendererFactoryFor", "getContextNames"])
            ->disableOriginalConstructor()
            ->getMock();
        $factory = $this->getMockBuilder(ILIAS\UI\Implementation\Render\RendererFactory::class)
            ->getMock();

        $rendered_component = $this->createMock(ILIAS\UI\Component\Component::class);

        $component1 = $this->createMock(ILIAS\UI\Component\Component::class);
        $component2 = $this->createMock(ILIAS\UI\Component\Component::class);
        $component_name1 = "COMPONENT 1";
        $component_name2 = "COMPONENT 2";

        $loader
            ->expects($this->once())
            ->method("getContextNames")
            ->with([$component1, $component2])
            ->willReturn([$component_name1, $component_name2]);

        $loader
            ->expects($this->once())
            ->method("getRendererFactoryFor")
            ->with($rendered_component)
            ->willReturn($factory);

        $renderer = $this->createMock(ComponentRenderer::class);
        $factory
            ->expects($this->once())
            ->method("getRendererInContext")
            ->with($rendered_component, [$component_name1, $component_name2])
            ->willReturn($renderer);

        $renderer2 = $loader->getRendererFor($rendered_component, [$component1, $component2]);
        $this->assertEquals($renderer, $renderer2);
    }

    public function testGetRendererFactoryForFormInput(): void
    {
        $component_mock = $this->createMock(FormInput::class);
        $factory = $this->fs_loader->getRendererFactoryFor($component_mock);
        $this->assertSame($factory, $this->field_renderer_factory);
    }

    public function testGetRendererFactoryForMessageBox(): void
    {
        $component_mock = $this->createMock(MessageBox::class);
        $factory = $this->fs_loader->getRendererFactoryFor($component_mock);
        $this->assertSame($factory, $this->message_box_renderer_factory);
    }

    public function testGetRendererFactoryForForm(): void
    {
        $component_mock = $this->createMock(Form::class);
        $factory = $this->fs_loader->getRendererFactoryFor($component_mock);
        $this->assertSame($factory, $this->form_renderer_factory);
    }

    public function testGetRendererFactoryForMenu(): void
    {
        $component_mock = $this->createMock(Menu::class);
        $factory = $this->fs_loader->getRendererFactoryFor($component_mock);
        $this->assertSame($factory, $this->menu_renderer_factory);
    }

    public function testGetRendererFactoryForButton(): void
    {
        $component_mock = $this->createMock(Button::class);
        $factory = $this->fs_loader->getRendererFactoryFor($component_mock);
        $this->assertSame($factory, $this->button_renderer_factory);
    }

    public function testGetRendererFactoryForOther(): void
    {
        $component_mock = $this->createMock(Component::class);
        $factory = $this->fs_loader->getRendererFactoryFor($component_mock);
        $this->assertSame($factory, $this->default_renderer_factory);
    }
}
