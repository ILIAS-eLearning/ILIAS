<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

/**
 * Test on glyph implementation.
 */
class GlyphTest extends ILIAS_UI_TestBase {
	public function getGlyphFactory() {
		return new \ILIAS\UI\Implementation\Component\Glyph\Factory();
	}

	public function getCounterFactory() {
		return new \ILIAS\UI\Implementation\Component\Counter\Factory();
	}

	static $canonical_css_classes = array
		( C\Glyph\Glyph::UP			=>	 "glyphicon glyphicon-chevron-up"
		, C\Glyph\Glyph::DOWN		=>	 "glyphicon glyphicon-chevron-down"
		, C\Glyph\Glyph::ADD		=>	 "glyphicon glyphicon-plus"
		, C\Glyph\Glyph::REMOVE		=>	 "glyphicon glyphicon-minus"
		, C\Glyph\Glyph::PREVIOUS	=>	 "glyphicon glyphicon-chevron-left"
		, C\Glyph\Glyph::NEXT		=>	 "glyphicon glyphicon-chevron-right"
		, C\Glyph\Glyph::CALENDAR	=>	 "glyphicon glyphicon-calendar"
		, C\Glyph\Glyph::CLOSE		=>	 "glyphicon glyphicon-remove"
		, C\Glyph\Glyph::ATTACHMENT	=>	 "glyphicon glyphicon-paperclip"
		, C\Glyph\Glyph::CARET		=>	 "caret"
		, C\Glyph\Glyph::DRAG		=>	 "glyphicon glyphicon-share-alt"
		, C\Glyph\Glyph::SEARCH		=>	 "glyphicon glyphicon-search"
		, C\Glyph\Glyph::FILTER		=>	 "glyphicon glyphicon-filter"
		, C\Glyph\Glyph::INFO		=>	 "glyphicon glyphicon-info-sign"
		, C\Glyph\Glyph::ENVELOPE	=>	 "glyphicon glyphicon-envelope"
		);

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_implements_factory_interface($factory_method) {
		$f = $this->getGlyphFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Glyph\\Factory", $f);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Glyph\\Glyph", $f->$factory_method("http://www.ilias.de"));
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_glyph_types($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method("http://www.ilias.de");

		$this->assertNotNull($g);
		$this->assertEquals($factory_method, $g->getType());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_no_counter($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method("http://www.ilias.de");

		$this->assertCount(0, $g->getCounters());
	}

	/**
	 * @dataProvider counter_type_provider
	 */
	public function test_one_counter($counter_type) {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$number = 1;

		$g = $gf
			->filter("http://www.ilias.de")
			->withCounter(
				$cf->$counter_type($number)
			);

		$counters = $g->getCounters();
		$this->assertCount(1, $counters);
		$c = $counters[0];
		$this->assertEquals($counter_type, $c->getType());
		$this->assertEquals($number, $c->getNumber());
	}

	public function test_two_counters() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$number_s = 1;
		$number_n = 2;

		$g = $gf
			->attachment("http://www.ilias.de")
			->withCounter(
				$cf->status($number_s)
			)
			->withCounter(
				$cf->novelty($number_n)
			);

		$counters = $g->getCounters();
		$this->assertCount(2, $counters);
		$vals = array_map(function($c) {
			return array($c->getType(), $c->getNumber());
		}, $counters);
		$this->assertContains(array("status", $number_s), $vals);
		$this->assertContains(array("novelty", $number_n), $vals);
	}

	public function test_only_two_counters() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$number_s = 1;
		$number_n1 = 2;
		$number_n2 = 2;

		$g = $gf
			->attachment("http://www.ilias.de")
			->withCounter(
				$cf->status($number_s)
			)
			->withCounter(
				$cf->novelty($number_n1)
			)
			->withCounter(
				$cf->novelty($number_n2)
			);

		$counters = $g->getCounters();
		$this->assertCount(2, $counters);
		$vals = array_map(function($c) {
			return array($c->getType(), $c->getNumber());
		}, $counters);
		$this->assertContains(array("status", $number_s), $vals);
		$this->assertContains(array("novelty", $number_n2), $vals);
	}

	public function test_immutability_withCounter() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();

		$g = $gf->filter("http://www.ilias.de");
		$g2 = $g
			->withCounter(
				$cf->novelty(0)
			);

		$counters = $g->getCounters();
		$this->assertCount(0, $counters);
	}

	public function test_known_glyphs_only() {
		try {
			new \ILIAS\UI\Implementation\Component\Glyph\Glyph("FOO");
			$this->assertFalse("We should not get here");
		}
		catch (\InvalidArgumentException $e) {}
	}

	public function test_known_glyphs_only_withType() {
		$gf = $this->getGlyphFactory();

		try {
			$gf->up("http://www.ilias.de")->withType("FOO");
			$this->assertFalse("We should not get here");
		}
		catch (\InvalidArgumentException $e) {}
	}

	public function test_withType() {
		$gf = $this->getGlyphFactory();
		$g = $gf
			->up("http://www.ilias.de")
			->withType(C\Glyph\Glyph::DOWN);
		$this->assertEquals(C\Glyph\Glyph::DOWN, $g->getType());
	}

	public function test_immutability_withType() {
		$gf = $this->getGlyphFactory();
		$g = $gf->up("http://www.ilias.de");
		$g2 = $g->withType(C\Glyph\Glyph::DOWN);
		$this->assertEquals(C\Glyph\Glyph::UP, $g->getType());
	}

	public function glyph_type_provider() {
		return array
			( array(C\Glyph\Glyph::UP)
			, array(C\Glyph\Glyph::DOWN)
			, array(C\Glyph\Glyph::ADD)
			, array(C\Glyph\Glyph::REMOVE)
			, array(C\Glyph\Glyph::PREVIOUS)
			, array(C\Glyph\Glyph::NEXT)
			, array(C\Glyph\Glyph::CALENDAR)
			, array(C\Glyph\Glyph::CLOSE)
			, array(C\Glyph\Glyph::ATTACHMENT)
			, array(C\Glyph\Glyph::CARET)
			, array(C\Glyph\Glyph::DRAG)
			, array(C\Glyph\Glyph::SEARCH)
			, array(C\Glyph\Glyph::FILTER)
			, array(C\Glyph\Glyph::INFO)
			, array(C\Glyph\Glyph::ENVELOPE)
			);
	}

	public function counter_type_provider() {
		return array
			( array("status")
			, array("novelty")
			);
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_render_simple($type) {
		$f = $this->getGlyphFactory();
		$r = $this->getDefaultRenderer();
		$c = $f->$type("http://www.ilias.de");

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[$type];
		$expected = "<span class=\"$css_classes\" aria-hidden=\"true\"></span>";
		$this->assertEquals($expected, $html);
	}

	/**
 	 * @dataProvider counter_type_provider
	 */
	public function test_render_withCounter($type) {
		$fg = $this->getGlyphFactory();
		$fc = $this->getCounterFactory();
		$r = $this->getDefaultRenderer();
		$c = $fg->envelope("http://www.ilias.de")->withCounter($fc->$type(42));

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[C\Glyph\Glyph::ENVELOPE];
		$expected = "<span class=\"$css_classes\" aria-hidden=\"true\"></span>".
					"<span class=\"badge badge-notify il-counter-$type\">42</span>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_withTwoCounters() {
		$fg = $this->getGlyphFactory();
		$fc = $this->getCounterFactory();
		$r = $this->getDefaultRenderer();
		$c = $fg->envelope("http:://www.ilias.de")
				->withCounter($fc->novelty(42))
				->withCounter($fc->status(7));

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[C\Glyph\Glyph::ENVELOPE];
		$expected = "<span class=\"$css_classes\" aria-hidden=\"true\"></span>".
					"<span class=\"badge badge-notify il-counter-status\">7</span>".
					"<span class=\"badge badge-notify il-counter-novelty\">42</span>";
		$this->assertEquals($expected, $html);
	}

	public function test_dont_render_counter() {
		$r = new \ILIAS\UI\Implementation\Component\Glyph\Renderer($this->getUIFactory(), $this->getTemplateFactory());
		$f = $this->getCounterFactory();

		try {
			$r->render($f->status(0), $this->getDefaultRenderer());
			$this->assertFalse("This should not happen!");
		}
		catch (\LogicException $e) {}
	}
}
