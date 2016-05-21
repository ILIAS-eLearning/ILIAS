<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

/**
 * Test on default renderer. 
 */
class DefaultRendererTest extends ILIAS_UI_TestBase {
	public function test_instantiateRenderer_successfully() {
		// There should be a renderer for Glyph...
		$dr = $this->getDefaultRenderer();
		$r = $dr->instantiateRendererFor("\\ILIAS\\UI\\Implementation\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Renderer", $r);
	}

	public function test_instantiateRenderer_unsuccessfully() {
		// There should be no renderer for Counter...
		$dr = $this->getDefaultRenderer();
		try {
			$r = $dr->instantiateRendererFor("\\ILIAS\\UI\\Implementation\\Counter\\Counter");
			$this->assertFalse("We should not get here");
		} catch (\LogicException $e) {}
	}

	public function test_getRenderer_successfully() {
		// There should be a renderer for Glyph...
		$dr = $this->getDefaultRenderer();
		$r = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Glyph\\Glyph");
		$this->assertInstanceOf("\\ILIAS\\UI\\Renderer", $r);
	}

	public function test_getRenderer_unsuccessfully() {
		// There should be no renderer for Counter...
		$dr = $this->getDefaultRenderer();
		try {
			$r = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Counter\\Counter");
			$this->assertFalse("We should not get here");
		} catch (\LogicException $e) {}
	}

	public function test_getRenderer_caching() {
		$dr = $this->getDefaultRenderer();
		$r1 = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Glyph\\Glyph");
		$r2 = $dr->getRendererFor("\\ILIAS\\UI\\Implementation\\Glyph\\Glyph");
		$this->assertTrue($r1 === $r2, "Instances not equal");
	}
}
