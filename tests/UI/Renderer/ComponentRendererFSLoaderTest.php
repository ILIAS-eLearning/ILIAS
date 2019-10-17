<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/TestComponent.php");

use \ILIAS\UI\Implementation as I;

class ComponentRendererFSLoaderTesting extends ILIAS\UI\Implementation\Render\FSLoader
{
    public function _instantiateRendererFor($class)
    {
        return $this->instantiateRendererFor($class);
    }
}

class ComponentRendererFSLoaderTest extends PHPUnit_Framework_TestCase
{
    protected function getComponentRendererFSLoader()
    {
        $ui_factory = $this->getMockBuilder(ILIAS\UI\Factory::class)->getMock();
        $tpl_factory = $this->getMockBuilder(I\Render\TemplateFactory::class)->getMock();
        $lng = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
        $js_binding = $this->getMockBuilder(I\Render\JavaScriptBinding::class)->getMock();
        $default_renderer_factory = new I\Render\DefaultRendererFactory($ui_factory, $tpl_factory, $lng, $js_binding);
        $this->glyph_renderer = $this->createMock(I\Render\RendererFactory::class);
        return new ComponentRendererFSLoaderTesting($default_renderer_factory, $this->glyph_renderer);
    }

    public function test_getRenderer_successfully()
    {
        // There should be a renderer for Glyph...
        $f = $this->getComponentRendererFSLoader();
        $component = new I\Component\Button\Standard("", "");
        $r = $f->getRendererFor($component, []);
        $this->assertInstanceOf(I\Render\ComponentRenderer::class, $r);
    }

    public function test_getRenderer_successfully_extra()
    {
        // There should be a renderer for Glyph...
        $f = $this->getComponentRendererFSLoader();
        $component = new I\Component\Glyph\Glyph("up", "up");
        $context = $this->createMock(\ILIAS\UI\Component\Component::class);
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

    public function test_getRenderer_uses_RendererFactory()
    {
        $loader = $this->getMockBuilder(ILIAS\UI\Implementation\Render\FSLoader::class)
            ->setMethods(["getRendererFactoryFor", "getContextNames"])
            ->disableOriginalConstructor()
            ->getMock();
        $factory = $this->getMockBuilder(ILIAS\UI\Implementation\RendererFactory::class)
            ->setMethods(["getRendererInContext"])
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

        $renderer = "RENDERER";
        $factory
            ->expects($this->once())
            ->method("getRendererInContext")
            ->with($rendered_component, [$component_name1, $component_name2])
            ->willReturn($renderer);

        $renderer2 = $loader->getRendererFor($rendered_component, [$component1, $component2]);
        $this->assertEquals($renderer, $renderer2);
    }
}
