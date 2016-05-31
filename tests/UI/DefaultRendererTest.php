<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
}
