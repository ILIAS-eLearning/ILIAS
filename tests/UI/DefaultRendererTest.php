<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Test {
require_once("libs/composer/vendor/autoload.php");

use \ILIAS\UI\Implementation\Render\ResourceRegistry;
use \ILIAS\UI\Renderer as DefaultRenderer;
use \ILIAS\UI\Component\Component;

class TestComponent implements \ILIAS\UI\Component\Component {
	public function __construct($text) {
		$this->text = $text;
	}
}

class Renderer implements \ILIAS\UI\Implementation\Render\ComponentRenderer {
	public function render(Component $component, DefaultRenderer $default_renderer) {
		return $component->text;
	}

	public function registerResources(ResourceRegistry $registry) {
		$registry->register("test.js");
	}
}

} // namespace \ILIAS\UI\Test

namespace {

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

class DefaultRendererTest extends ILIAS_UI_TestBase {
	public function test_instantiateRenderer_successfully() {
		// There should be a renderer for Glyph...
		$dr = $this->getDefaultRenderer();
		$r = $dr->instantiateRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Implementation\\Render\\ComponentRenderer", $r);
	}

	public function test_getRenderer_successfully() {
		// There should be a renderer for Glyph...
		$dr = $this->getDefaultRenderer();
		$r = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Implementation\\Render\\ComponentRenderer", $r);
	}

	public function test_getRenderer_caching() {
		$dr = $this->getDefaultRenderer();
		$r1 = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$r2 = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertTrue($r1 === $r2, "Instances not equal");
	}

	public function test_getRendererNameFor() {
		$dr = $this->getDefaultRenderer();

		$renderer_class = $dr->getRendererNameFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$expected = "\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Renderer";
		$this->assertEquals($expected, $renderer_class);
	}

	public function getResourceRegistry() {
		$this->resource_registry = parent::getResourceRegistry();
		return $this->resource_registry;
	}

	public function test_invokesRegistry() {
		$dr = $this->getDefaultRenderer();
		$component = new \ILIAS\UI\Test\TestComponent("foo");

		$dr->render($component);

		$this->assertEquals(array("test.js"), $this->resource_registry->resources);
	}
}

} // root namespace
