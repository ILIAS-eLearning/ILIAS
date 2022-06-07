<?php declare(strict_types=1);

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
 
require_once(__DIR__ . "/TestComponent.php");

use ILIAS\UI\Implementation as I;
use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Component;
use ILIAS\Refinery\Factory as Refinery;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\UI\Implementation\Render\FSLoader;
use ILIAS\UI\Implementation\Render\ComponentRenderer;

class ComponentRendererFSLoaderTest extends TestCase
{
    /**
     * @var I\Render\RendererFactory|mixed|MockObject
     */
    private $glyph_renderer;
    /**
     * @var I\Render\RendererFactory|mixed|MockObject
     */
    private $icon_renderer;

    protected function getComponentRendererFSLoader() : FSLoader
    {
        $ui_factory = $this->getMockBuilder(ILIAS\UI\Factory::class)->getMock();
        $tpl_factory = $this->getMockBuilder(I\Render\TemplateFactory::class)->getMock();
        $lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $js_binding = $this->getMockBuilder(I\Render\JavaScriptBinding::class)->getMock();
        $refinery_mock = $this->getMockBuilder(Refinery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $image_path_resolver = $this->getMockBuilder(ILIAS\UI\Implementation\Render\ImagePathResolver::class)
                ->getMock();

        $default_renderer_factory = new I\Render\DefaultRendererFactory(
            $ui_factory,
            $tpl_factory,
            $lng,
            $js_binding,
            $refinery_mock,
            $image_path_resolver
        );
        $this->glyph_renderer = $this->createMock(I\Render\RendererFactory::class);
        $this->icon_renderer = $this->createMock(I\Render\RendererFactory::class);

        $field_renderer = $this->createMock(I\Render\RendererFactory::class);
        return new FSLoader($default_renderer_factory, $this->glyph_renderer, $this->icon_renderer, $field_renderer);
    }

    public function test_getRenderer_successfully() : void
    {
        // There should be a renderer for Glyph...
        $f = $this->getComponentRendererFSLoader();
        $component = new I\Component\Button\Standard("", "");
        $r = $f->getRendererFor($component, []);
        $this->assertInstanceOf(I\Render\ComponentRenderer::class, $r);
    }

    public function test_getRenderer_successfully_extra() : void
    {
        // There should be a renderer for Glyph...
        $f = $this->getComponentRendererFSLoader();
        $component = new I\Component\Symbol\Glyph\Glyph("up", "up");
        $context = $this->createMock(Component::class);
        $renderer = $this->createMock(I\Render\ComponentRenderer::class);

        $context_name = "foo";
        $context
            ->expects($this->once())
            ->method("getCanonicalName")
            ->willReturn($context_name);

        $this->glyph_renderer
            ->expects($this->once())
            ->method("getRendererInContext")
            ->with($component, [$context_name])
            ->willReturn($renderer);

        $r = $f->getRendererFor($component, [$context]);

        $this->assertEquals($renderer, $r);
    }

    public function test_getRenderer_uses_RendererFactory() : void
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
}
