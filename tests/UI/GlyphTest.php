<?php

require_once("libs/composer/vendor/autoload.php");

use \ILIAS\UI\Component as C;

class GlyphTestCustomException extends \Exception {};

/**
 * Test on glyph implementation.
 */
class GlyphTest extends PHPUnit_Framework_TestCase {
	public function getGlyphFactory() {
		return new \ILIAS\UI\Implementation\Glyph\Factory();
	}

	public function getCounterFactory() {
		return new \ILIAS\UI\Implementation\Counter\Factory();
	}

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
			( array("up")
			, array("down")
			, array("add")
			, array("remove")
			, array("previous")
			, array("next")
			, array("calendar")
			, array("close")
			, array("attachment")
			, array("caret")
			, array("drag")
			, array("search")
			, array("filter")
			, array("info")
			, array("envelope")
			);
	}

	public function counter_type_provider() {
		return array
			( array("status")
			, array("novelty")
			);
	}
}
