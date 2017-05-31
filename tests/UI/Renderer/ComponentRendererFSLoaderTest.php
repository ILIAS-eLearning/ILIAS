<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/TestComponent.php");

class ComponentRendererFSLoaderTesting extends ILIAS\UI\Implementation\Render\FSLoader {
    public function _instantiateRendererFor($class) {
        return $this->instantiateRendererFor($class);
    }
    public function _getRendererNamesFor($class, array $contexts) {
        return $this->getRendererNamesFor($class, $contexts);
    }
}

class ComponentRendererFSLoaderTest extends PHPUnit_Framework_TestCase {
    protected function getComponentRendererFSLoader() {
		$ui_factory = $this->getMockBuilder(ILIAS\UI\Factory::class)->getMock();
		$tpl_factory = $this->getMockBuilder(ILIAS\UI\Implementation\Render\TemplateFactory::class)->getMock();
		$lng = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
		$js_binding = $this->getMockBuilder(ILIAS\UI\Implementation\Render\JavaScriptBinding::class)->getMock();
        return new ComponentRendererFSLoaderTesting($ui_factory, $tpl_factory, $lng, $js_binding);
    }

	public function test_getRenderer_successfully() {
		// There should be a renderer for Glyph...
		$f = $this->getComponentRendererFSLoader();
		$r = $f->getRendererFor(new \ILIAS\UI\Implementation\Component\Glyph\Glyph("up", "up"), []);
		$this->assertInstanceOf(\ILIAS\UI\Implementation\Render\ComponentRenderer::class, $r);
	}

	public function test_getRendererNamesFor_no_context() {
		$f = $this->getComponentRendererFSLoader();

		$renderer_class = $f->_getRendererNamesFor(\ILIAS\UI\Implementation\Component\Glyph\Glyph::class, []);
		$expected = [\ILIAS\UI\Implementation\Component\Glyph\Renderer::class];
		$this->assertEquals($expected, $renderer_class);
	}

	public function test_getRendererNamesFor_with_contexts() {
		$f = $this->getComponentRendererFSLoader();

		$context_names = $f->_getRendererNamesFor("Space\\Name", ["A", "B"]);
		$expected =
			[ "Space\\Renderer_A_B"
			, "Space\\Renderer_B"
			, "Space\\Renderer_A"
			, "Space\\Renderer"
			];
		$this->assertEquals($expected, $context_names);
	}
}
