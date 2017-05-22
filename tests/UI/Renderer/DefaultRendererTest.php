<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/TestComponent.php");
require_once(__DIR__."/../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

class DefaultRendererTest extends ILIAS_UI_TestBase {
	public function test_getRenderer_successfully() {
		// There should be a renderer for Glyph...
		$dr = $this->getDefaultRenderer();
		$r = $dr->_getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Implementation\\Render\\ComponentRenderer", $r);
	}

	public function test_getRenderer_caching() {
		$dr = $this->getDefaultRenderer();
		$r1 = $dr->_getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$r2 = $dr->_getRendererFor("\\ILIAS\\UI\\Implementation\\Component\\Glyph\\Glyph");
		$this->assertTrue($r1 === $r2, "Instances not equal");
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

	public function test_withAdditionalContext_clones() {
		$dr = $this->getDefaultRenderer();
		$component = new \ILIAS\UI\Test\TestComponent("foo");
		$dr2 = $dr->withAdditionalContext($component);
		$this->assertNotSame($dr, $dr2);
	}

	public function test_getContexts() {
		$dr = $this->getDefaultRenderer();
		$c1 = new \ILIAS\UI\Test\TestComponent("foo");
		$c2 = new \ILIAS\UI\Test\TestComponent("bar");
		$dr2 = $dr
			->withAdditionalContext($c1)
			->withAdditionalContext($c2);
		$this->assertEquals([], $dr->_getContexts());
		$this->assertEquals([$c1, $c2], $dr2->_getContexts());
	}
}
