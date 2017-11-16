<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/TestComponent.php");

class ComponentRendererFSLoaderTesting extends ILIAS\UI\Implementation\Render\FSLoader {
    public function _instantiateRendererFor($class) {
        return $this->instantiateRendererFor($class);
    }
}

class ComponentRendererFSLoaderTest extends PHPUnit_Framework_TestCase {
    protected function getComponentRendererFSLoader() {
		$ui_factory = $this->getMockBuilder(ILIAS\UI\Factory::class)->getMock();
		$tpl_factory = $this->getMockBuilder(ILIAS\UI\Implementation\Render\TemplateFactory::class)->getMock();
		$lng = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
		$js_binding = $this->getMockBuilder(ILIAS\UI\Implementation\Render\JavaScriptBinding::class)->getMock();
		$default_renderer_factory = new \ILIAS\UI\Implementation\Render\DefaultRendererFactory($ui_factory, $tpl_factory, $lng, $js_binding);
        return new ComponentRendererFSLoaderTesting($default_renderer_factory);
    }

	public function test_getRenderer_successfully() {
		// There should be a renderer for Glyph...
		$f = $this->getComponentRendererFSLoader();
		$r = $f->getRendererFor(new \ILIAS\UI\Implementation\Component\Glyph\Glyph("up", "up"), []);
		$this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $r);
	}

	public function test_getRenderer_uses_RendererFactory() {
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
