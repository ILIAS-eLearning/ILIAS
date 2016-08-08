<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Test on button implementation.
 */
class ButtonTest extends ILIAS_UI_TestBase {
	public function getButtonFactory() {
		return new \ILIAS\UI\Implementation\Component\Button\Factory();
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
		$ln = "http://www.ilias.de";
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", $ln);
		$r = $this->getDefaultRenderer();

		$html = $this->normalizeHTML($r->render($b));

		$css_classes = self::$canonical_css_classes[$factory_method];
		$expected = "<a class=\"$css_classes \" href=\"$ln\" data-action=\"$ln\">".
					"label".
					"</a>";
		$this->assertEquals($expected, $html);
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_render_button_disabled($factory_method) {
		$ln = "http://www.ilias.de";
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", $ln)
				->withUnavailableAction();
		$r = $this->getDefaultRenderer();

		$html = $this->normalizeHTML($r->render($b));

		$css_classes = self::$canonical_css_classes[$factory_method];
		$expected = "<a class=\"$css_classes ilSubmitInactive\"  data-action=\"$ln\">".
					"label".
					"</a>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_close_button() {
		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$b = $f->close();

		$html = $this->normalizeHTML($r->render($b));

		$expected = "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">".
					"	<span aria-hidden=\"true\">x</span>".
					"	<span class=\"sr-only\">Close</span>".
					"</button>";
		$this->assertEquals($expected, $html);
	}

	public function button_type_provider() {
		return array
			( array("standard")
			, array("primary")
			);
	}
}
