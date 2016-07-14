<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

/**
 * Test on button implementation.
 */
class ButtonTest extends ILIAS_UI_TestBase {
	public function getButtonFactory() {
		return new \ILIAS\UI\Implementation\Component\Button\Factory();
	}

	public function getGlyphFactory() {
		return new \ILIAS\UI\Implementation\Component\Glyph\Factory();
	}

	static $canonical_css_classes = array
		( "standard"	=>	 "btn btn-default"
		, "primary"	 =>	 "btn btn-default btn-primary"
		);

	public function test_implements_factory_interface() {
		$f = $this->getButtonFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Factory", $f);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Standard"
			, $f->standard("label", "http://www.ilias.de")
			);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Primary"
			, $f->primary("label", "http://www.ilias.de")
			);
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Close"
			, $f->close()
			);
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_label_or_glyph_only($factory_method) {
		$f = $this->getButtonFactory();
		try {
			$f->$factory_method($this, "http://www.ilias.de");
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {}
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_string_action_only($factory_method) {
		$f = $this->getButtonFactory();
		try {
			$f->$factory_method("label", $this);
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {}
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_label($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de");

		$this->assertEquals("label", $b->getLabel());
		$this->assertEquals(null, $b->getGlyph());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_with_label($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de");

		$b2 = $b->withLabel("label2");	

		$this->assertEquals("label", $b->getLabel());
		$this->assertEquals("label2", $b2->getLabel());
		$this->assertEquals(null, $b->getGlyph());
		$this->assertEquals(null, $b2->getGlyph());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_glyph($factory_method) {
		$f = $this->getButtonFactory();
		$gf = $this->getGlyphFactory();
		$g = $gf->mail();
		$b = $f->$factory_method($g, "http://www.ilias.de");

		$this->assertEquals(null, $b->getLabel());
		$this->assertEquals($g, $b->getGlyph());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_with_glyph($factory_method) {
		$f = $this->getButtonFactory();
		$gf = $this->getGlyphFactory();
		$g = $gf->mail();
		$g2 = $gf->info("http://www.ilias.de");
		$b = $f->$factory_method($g, "http://www.ilias.de");

		$b2 = $b->withGlyph($g2);	

		$this->assertEquals(null, $b->getLabel());
		$this->assertEquals(null, $b2->getLabel());
		$this->assertEquals($g, $b->getGlyph());
		$this->assertEquals($g2, $b2->getGlyph());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_label_glyph($factory_method) {
		$f = $this->getButtonFactory();
		$gf = $this->getGlyphFactory();
		$g = $gf->mail();
		$b = $f->$factory_method("label", "http://www.ilias.de")
				->withGlyph($g);

		$this->assertEquals("label", $b->getLabel());
		$this->assertEquals($g, $b->getGlyph());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_action($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de");

		$this->assertEquals("http://www.ilias.de", $b->getAction());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_activated_on_default($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de");

		$this->assertTrue($b->isActive());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_deactivation($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de")
				->withUnavailableAction();

		$this->assertFalse($b->isActive());
		$this->assertEquals("http://www.ilias.de", $b->getAction());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_render_button_label($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de");
		$r = $this->getDefaultRenderer();

		$html = $this->normalizeHTML($r->render($b));

		$css_classes = self::$canonical_css_classes[$factory_method];
		$expected = "<a class=\"$css_classes\" href=\"http://www.ilias.de\">".
					"label".
					"</a>";
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_render_button_glyph($factory_method) {
		$f = $this->getButtonFactory();
		$gf = $this->getGlyphFactory();
		$g = $gf->mail();
		$b = $f->$factory_method($g, "http://www.ilias.de");
		$r = $this->getDefaultRenderer();

		$html = $this->normalizeHTML($r->render($b));

		$css_classes = self::$canonical_css_classes[$factory_method];
		$expected = "<a class=\"$css_classes\" href=\"http://www.ilias.de\">".
					"label".
					"</a>";
	}

	public function button_type_provider() {
		return array
			( array("standard")
			, array("primary")
			);
	}
}
