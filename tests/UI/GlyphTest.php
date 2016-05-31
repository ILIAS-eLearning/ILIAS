<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Glyph\Renderer as GlyphRenderer;

class GlyphTestCustomException extends \Exception {};

/**
 * Test on glyph implementation.
 */
class GlyphTest extends ILIAS_UI_TestBase {
	public function getGlyphFactory() {
		return new \ILIAS\UI\Implementation\Glyph\Factory();
	}

	public function getCounterFactory() {
		return new \ILIAS\UI\Implementation\Counter\Factory();
	}

	static $canonical_css_classes = array
		( C\Glyph::UP			=>	 "glyphicon glyphicon-chevron-up"
		, C\Glyph::DOWN			=>	 "glyphicon glyphicon-chevron-down"
		, C\Glyph::ADD			=>	 "glyphicon glyphicon-plus"
		, C\Glyph::REMOVE		=>	 "glyphicon glyphicon-minus"
		, C\Glyph::PREVIOUS		=>	 "glyphicon glyphicon-chevron-left"
		, C\Glyph::NEXT			=>	 "glyphicon glyphicon-chevron-right"
		, C\Glyph::CALENDAR		=>	 "glyphicon glyphicon-calendar"
		, C\Glyph::CLOSE		=>	 "glyphicon glyphicon-remove"
		, C\Glyph::ATTACHMENT	=>	 "glyphicon glyphicon-paperclip"
		, C\Glyph::CARET		=>	 "caret"
		, C\Glyph::DRAG			=>	 "glyphicon glyphicon-share-alt"
		, C\Glyph::SEARCH		=>	 "glyphicon glyphicon-search"
		, C\Glyph::FILTER		=>	 "glyphicon glyphicon-filter"
		, C\Glyph::INFO			=>	 "glyphicon glyphicon-info-sign"
		, C\Glyph::ENVELOPE		=>	 "glyphicon glyphicon-envelope"
		);

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_implements_factory_interface($factory_method) {
		$f = $this->getGlyphFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Factory\\Glyph", $f);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Glyph", $f->$factory_method());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_glyph_types($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method();

		$this->assertNotNull($g);
		$this->assertEquals($factory_method, $g->getType());
	}

	/**
	 * @dataProvider glyph_type_provider
	 */
	public function test_no_counter($factory_method) {
		$f = $this->getGlyphFactory();
		$g = $f->$factory_method();

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
			->filter()
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
			->attachment()
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
			->attachment()
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

		$g = $gf->filter();
		$g2 = $g
			->withCounter(
				$cf->novelty(0)
			);

		$counters = $g->getCounters();
		$this->assertCount(0, $counters);
	}

	public function test_known_glyphs_only() {
		// TODO: move this pattern to ILIAS_UI_TestBase
		assert_options(ASSERT_CALLBACK, function () {
			throw new GlyphTestCustomException();
		});

		try {
			new \ILIAS\UI\Implementation\Glyph\Glyph("FOO");
			$this->assertFalse("We should not get here");
		}
		catch (GlyphTestCustomException $e) {}
	}

	public function test_known_glyphs_only_withType() {
		// TODO: move this pattern to ILIAS_UI_TestBase
		assert_options(ASSERT_CALLBACK, function () {
			throw new GlyphTestCustomException();
		});
		$gf = $this->getGlyphFactory();

		try {
			$gf->up()->withType("FOO");
			$this->assertFalse("We should not get here");
		}
		catch (GlyphTestCustomException $e) {}
	}

	public function test_withType() {
		$gf = $this->getGlyphFactory();
		$g = $gf
			->up()
			->withType(C\Glyph::DOWN);
		$this->assertEquals(C\Glyph::DOWN, $g->getType());
	}

	public function test_immutability_withType() {
		$gf = $this->getGlyphFactory();
		$g = $gf->up();
		$g2 = $g->withType(C\Glyph::DOWN);
		$this->assertEquals(C\Glyph::UP, $g->getType());
	}

	public function glyph_type_provider() {
		return array
			( array(C\Glyph::UP)
			, array(C\Glyph::DOWN)
			, array(C\Glyph::ADD)
			, array(C\Glyph::REMOVE)
			, array(C\Glyph::PREVIOUS)
			, array(C\Glyph::NEXT)
			, array(C\Glyph::CALENDAR)
			, array(C\Glyph::CLOSE)
			, array(C\Glyph::ATTACHMENT)
			, array(C\Glyph::CARET)
			, array(C\Glyph::DRAG)
			, array(C\Glyph::SEARCH)
			, array(C\Glyph::FILTER)
			, array(C\Glyph::INFO)
			, array(C\Glyph::ENVELOPE)
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
		$c = $f->$type();

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
		$c = $fg->envelope()->withCounter($fc->$type(42));

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[C\Glyph::ENVELOPE];
		$expected = "<span class=\"$css_classes\" aria-hidden=\"true\"></span>".
					"<span class=\"badge badge-notify il-counter-$type\">42</span>";
		$this->assertEquals($expected, $html);
	}

	public function test_render_withTwoCounters() {
		$fg = $this->getGlyphFactory();
		$fc = $this->getCounterFactory();
		$r = $this->getDefaultRenderer();
		$c = $fg->envelope()
				->withCounter($fc->novelty(42))
				->withCounter($fc->status(7));

		$html = $this->normalizeHTML($r->render($c));

		$css_classes = self::$canonical_css_classes[C\Glyph::ENVELOPE];
		$expected = "<span class=\"$css_classes\" aria-hidden=\"true\"></span>".
					"<span class=\"badge badge-notify il-counter-status\">7</span>".
					"<span class=\"badge badge-notify il-counter-novelty\">42</span>";
		$this->assertEquals($expected, $html);
	}
}
