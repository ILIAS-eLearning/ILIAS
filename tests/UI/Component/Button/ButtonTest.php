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
		( "standard" =>	 "btn btn-default"
		, "primary"	 =>	 "btn btn-default btn-primary"
		, "shy"	 =>	 "btn btn-link"
		, "tag"	 =>	 "btn btn-tag btn-tag-relevance-veryhigh"
		);

	static $canonical_css_inactivation_classes = array
		( "standard" =>	"ilSubmitInactive disabled"
		, "primary"	=> "ilSubmitInactive disabled"
		, "shy"	=> "ilSubmitInactive disabled"
		, "tag"	=> "btn-tag-inactive"
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
		$this->assertInstanceOf
			( "ILIAS\\UI\\Component\\Button\\Shy"
			, $f->shy("label", "http://www.ilias.de")
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
		$expected = "<a class=\"$css_classes\" href=\"$ln\" data-action=\"$ln\">".
					"label".
					"</a>";
		$this->assertHTMLEquals($expected, $html);
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
		$css_class_inactive = self::$canonical_css_inactivation_classes[$factory_method];
		$expected = "<a class=\"$css_classes $css_class_inactive\" data-action=\"$ln\">".
					"label".
					"</a>";
		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_close_button() {
		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$b = $f->close();

		$html = $this->normalizeHTML($r->render($b));

		$expected = "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">".
					"	<span aria-hidden=\"true\">&times;</span>".
					"	<span class=\"sr-only\">Close</span>".
					"</button>";
		$this->assertEquals($expected, $html);
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_render_button_with_on_load_code($factory_method) {
		$ln = "http://www.ilias.de";
		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$ids = array();
		$b = $f->$factory_method("label", $ln)
				->withOnLoadCode(function($id) use (&$ids) {
					$ids[] = $id;
					return "";
				});

		$html = $this->normalizeHTML($r->render($b));

		$this->assertCount(1, $ids);

		$id = $ids[0];
		$css_classes = self::$canonical_css_classes[$factory_method];
		$expected = "<a class=\"$css_classes\" href=\"$ln\" data-action=\"$ln\" id=\"$id\">".
					"label".
					"</a>";
		$this->assertHTMLEquals($expected, $html);
	}

	public function test_____render_close_button_with_on_load_code() {
		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$ids = array();
		$b = $f->close()
				->withOnLoadCode(function($id) use (&$ids) {
					$ids[] = $id;
					return "";
				});

		$html = $this->normalizeHTML($r->render($b));

		$this->assertCount(1, $ids);

		$id = $ids[0];
		$expected = "<button type=\"button\" class=\"close\" data-dismiss=\"modal\" id=\"$id\">".
					"	<span aria-hidden=\"true\">&times;</span>".
					"	<span class=\"sr-only\">Close</span>".
					"</button>";
		$this->assertEquals($expected, $html);
	}

	public function test_btn_tag_relevance() {
		$f = $this->getButtonFactory();
		$b = $f->tag('tag', '#');
		try {
			$b->withRelevance(0);
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
		try {
			$b->withRelevance('notsoimportant');
			$this->assertFalse("This should not happen");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

	public function test_render_btn_tag_relevance() {
		$expectations = array(
			'<a class="btn btn-tag btn-tag-relevance-verylow" href="#" data-action="#">tag</a>',
			'<a class="btn btn-tag btn-tag-relevance-low" href="#" data-action="#">tag</a>',
			'<a class="btn btn-tag btn-tag-relevance-middle" href="#" data-action="#">tag</a>',
			'<a class="btn btn-tag btn-tag-relevance-high" href="#" data-action="#">tag</a>',
			'<a class="btn btn-tag btn-tag-relevance-veryhigh" href="#" data-action="#">tag</a>'
		);

		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$t = $f->tag('tag', '#');
		$possible_relevances = array(
			$t::REL_VERYLOW,
			$t::REL_LOW,
			$t::REL_MID,
			$t::REL_HIGH,
			$t::REL_VERYHIGH
		);
		foreach($possible_relevances as $w) {
			$html = $this->normalizeHTML(
				$r->render($t->withRelevance($w))
			);
			$expected = $expectations[array_search($w, $possible_relevances)];
			$this->assertEquals($expected, $html);
		}
	}

	public function test_render_btn_tag_colors() {
		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$df = new \ILIAS\Data\Factory;

		$bgcol = $df->color('#00ff00');

		$b = $f->tag('tag', '#')
			->withBackgroundColor($bgcol);
		$html = $this->normalizeHTML($r->render($b));
		$expected = '<a class="btn btn-tag btn-tag-relevance-veryhigh" style="background-color: #00ff00; color: #000000;" href="#" data-action="#">tag</a>';
		$this->assertEquals($expected, $html);

		$fcol = $df->color('#ddd');
		$b = $b->withForegroundColor($fcol);
		$html = $this->normalizeHTML($r->render($b));
		$expected = '<a class="btn btn-tag btn-tag-relevance-veryhigh" style="background-color: #00ff00; color: #dddddd;" href="#" data-action="#">tag</a>';
		$this->assertEquals($expected, $html);
	}

	public function test_render_btn_tag_classes() {
		$f = $this->getButtonFactory();
		$r = $this->getDefaultRenderer();
		$df = new \ILIAS\Data\Factory;

		$classes = array('cl1', 'cl2');
		$b = $f->tag('tag', '#')
			->withClasses($classes);
		$this->assertEquals($classes, $b->getClasses());

		$html = $this->normalizeHTML($r->render($b));
		$expected = '<a class="btn btn-tag btn-tag-relevance-veryhigh cl1 cl2" href="#" data-action="#">tag</a>';
		$this->assertEquals($expected, $html);
	}
	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_with_aria_label($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de")->withAriaLabel("ariatext");
		$this->assertEquals("ariatext", $b->getAriaLabel());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_button_with_aria_checked($factory_method) {
		$f = $this->getButtonFactory();
		$b = $f->$factory_method("label", "http://www.ilias.de");
		$this->assertEquals(false, $b->isAriaChecked());
		$b2 = $f->$factory_method("label", "http://www.ilias.de")->withAriaChecked();
		$this->assertEquals(true,$b2->isAriaChecked());
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_render_button_with_aria_label($factory_method) {
		//only standard buttons have aria labels in the template. Should the others accept aria stuff?
		//if yes, remove this conditional
		if($factory_method == "standard")
		{
			$ln = "http://www.ilias.de";
			$f = $this->getButtonFactory();
			$r = $this->getDefaultRenderer();
			$b = $f->$factory_method("label", $ln)->withAriaLabel("aria label text");
			$aria_label = $b->getAriaLabel();

			$html = $this->normalizeHTML($r->render($b));
			$css_classes = self::$canonical_css_classes[$factory_method];
			$expected = "<a class=\"$css_classes\" href=\"$ln\" aria-label=\"$aria_label\" data-action=\"$ln\">".
				"label".
				"</a>";
			$this->assertHTMLEquals($expected, $html);
		}
	}

	/**
	 * @dataProvider button_type_provider
	 */
	public function test_render_button_with_aria_checked($factory_method) {
		//only standard buttons have aria labels in the template. Should the others accept aria stuff?
		//if yes, remove this conditional
		if($factory_method == "standard")
		{
			$ln = "http://www.ilias.de";
			$f = $this->getButtonFactory();
			$r = $this->getDefaultRenderer();
			$b = $f->$factory_method("label", $ln)->withAriaChecked();

			$html = $this->normalizeHTML($r->render($b));
			$css_classes = self::$canonical_css_classes[$factory_method];
			$expected = "<a class=\"$css_classes\" href=\"$ln\" aria-checked=\"true\" data-action=\"$ln\">".
				"label".
				"</a>";
			$this->assertHTMLEquals($expected, $html);
		}
	}


	public function button_type_provider() {
		return array
			( array("standard")
			, array("primary")
			, array("shy")
			, array("tag")
			);
	}

}
