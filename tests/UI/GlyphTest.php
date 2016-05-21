<?php

require_once("libs/composer/vendor/autoload.php");

class CustomExceptions extends \Exception {};

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
		$amount = 1;

		$g = $gf
			->filter()
			->withCounter(
				$cf->$counter_type($amount)
			);

		$counters = $g->getCounters();
		$this->assertCount(1, $counters);
		$c = $counters[0];
		$this->assertEquals($counter_type, $c->getType());
		$this->assertEquals($amount, $c->getAmount());
	}

	public function test_two_counters() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$amount_s = 1;
		$amount_n = 2;

		$g = $gf
			->attachment()
			->withCounter(
				$cf->status($amount_s)
			)
			->withCounter(
				$cf->novelty($amount_n)
			);

		$counters = $g->getCounters();
		$this->assertCount(2, $counters);
		$vals = array_map(function($c) {
			return array($c->getType(), $c->getAmount());
		}, $counters);
		$this->assertContains(array("status", $amount_s), $counters);
		$this->assertContains(array("novelty", $amount_n), $counters);
	}

	public function test_only_two_counters() {
		$gf = $this->getGlyphFactory();
		$cf = $this->getCounterFactory();
		$amount_s = 1;
		$amount_n1 = 2;
		$amount_n2 = 2;

		$g = $gf
			->attachment()
			->withCounter(
				$cf->status($amount_s)
			)
			->withCounter(
				$cf->novelty($amount_n1)
			)
			->withCounter(
				$cf->novelty($amount_n2)
			);

		$counters = $g->getCounters();
		$this->assertCount(2, $counters);
		$vals = array_map(function($c) {
			return array($c->getType(), $c->getAmount());
		}, $counters);
		$this->assertContains(array("status", $amount_s), $counters);
		$this->assertContains(array("novelty", $amount_n2), $counters);
	}

	public function test_known_glyphs_only() {
		assert_options(ASSERT_CALLBACK, function () {
			throw new CustomException();
		});

		try {
			new \ILIAS\UI\Implementation\Glyph\Glyph("PETER", array());
			$this->assertFalse("We should not get here");
		}
		catch (CustomException $e) {}
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
