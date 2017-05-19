<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/TestComponent.php");
require_once(__DIR__."/../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

class ComponentRendererFSLoaderTesting extends ILIAS\UI\Implementation\ComponentRendererFSLoader {
    public function _instantiateRendererFor($class) {
        return $this->instantiateRendererFor($class);
    }
    public function _getRendererNameFor($class) {
        return $this->getRendererNameFor($class);
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

	public function test_instantiateRenderer_successfully() {
		// There should be a renderer for Glyph...
		$l = $this->getComponentRendererFSLoader();
		$r = $l->_instantiateRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Implementation\\Render\\ComponentRenderer", $r);
	}

	public function test_getRenderer_successfully() {
		// There should be a renderer for Glyph...
		$f = $this->getComponentRendererFSLoader();
		$r = $f->getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Implementation\\Render\\ComponentRenderer", $r);
	}

	public function test_getRendererNameFor() {
		$f = $this->getComponentRendererFSLoader();

		$renderer_class = $f->_getRendererNameFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$expected = "\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Renderer";
		$this->assertEquals($expected, $renderer_class);
	}
}
