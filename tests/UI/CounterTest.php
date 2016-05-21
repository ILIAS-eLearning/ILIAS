<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__."/Base.php");

use \ILIAS\UI\Component as C;

class CounterTestCustomException extends \Exception {};

/**
 * Defines tests that a counter implementation should pass.
 */
class CounterTest extends ILIAS_UI_TestBase {
	public function getCounterFactory() {
		return new \ILIAS\UI\Implementation\Counter\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getCounterFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Factory\\Counter", $f);

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Counter", $f->status(0));
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Counter", $f->novelty(0));
	}

	/**
	 * @dataProvider number_provider
	 */
	public function test_status_counter($number) {
		$f = $this->getCounterFactory();

		$c = $f->status($number);

		$this->assertNotNull($c);
		$this->assertEquals(C\Counter::STATUS, $c->getType());
		$this->assertEquals($number, $c->getNumber());
	}

	/**
	 * @dataProvider number_provider
	 */
	public function test_novelty_counter($number) {
		$f = $this->getCounterFactory();

		$c = $f->novelty($number);

		$this->assertNotNull($c);
		$this->assertEquals(C\Counter::NOVELTY, $c->getType());
		$this->assertEquals($number, $c->getNumber());
	}

	public function test_known_counters_only() {
		assert_options(ASSERT_CALLBACK, function () {
			throw new CounterTestCustomException();
		});

		try {
			new \ILIAS\UI\Implementation\Counter\Counter("FOO", 1);
			$this->assertFalse("We should not get here");
		}
		catch (CounterTestCustomException $e) {}
	}

	public function test_known_counters_only_withType() {
		// TODO: move this pattern to ILIAS_UI_TestBase
		assert_options(ASSERT_CALLBACK, function () {
			throw new CounterTestCustomException();
		});
		$f = $this->getCounterFactory();

		try {
			$f->status(1)->withType("FOO");
			$this->assertFalse("We should not get here");
		}
		catch (CounterTestCustomException $e) {}
	}

	public function test_withType() {
		$f = $this->getCounterFactory();

		$c = $f->novelty(0)->withType(C\Counter::STATUS);

		$this->assertEquals(C\Counter::STATUS, $c->getType());
	}

	public function test_immutability_withType() {
		$f = $this->getCounterFactory();

		$c = $f->novelty(0);
		$c2 = $c->withType(C\Counter::STATUS);

		$this->assertEquals(C\Counter::NOVELTY, $c->getType());
	}

	public function test_withNumber() {
		$f = $this->getCounterFactory();

		$c = $f->novelty(0)->withNumber(1);

		$this->assertEquals(1, $c->getNumber());
	}

	/**
	 * @dataProvider no_number_provider
	 */
	public function test_int_numbers_only($no_number) {
		$f = $this->getCounterFactory();
		$failed_assertions = 0;

		assert_options(ASSERT_CALLBACK, function() use (&$failed_assertions) {
			$failed_assertions++;
		});

		$f->status($no_number);
		$f->novelty($no_number);

		$this->assertEquals(2, $failed_assertions);
	}

	/**
	 * @dataProvider no_number_provider
	 */
	public function test_int_numbers_only_withNumber($no_number) {
		// TODO: move this pattern to ILIAS_UI_TestBase
		assert_options(ASSERT_CALLBACK, function () {
			throw new CounterTestCustomException();
		});
		$f = $this->getCounterFactory();

		try {
			$f->status(1)->withNumber($no_number);
			$this->assertFalse("We should not get here");
		}
		catch (CounterTestCustomException $e) {}
	}

	public function number_provider() {
		return array
			( array(-13)
			, array(0)
			, array(23)
			, array(42)
			);
	}

	public function no_number_provider() {
		return array
			( array("foo")
			, array(9.1)
			, array(array())
			, array(new stdClass())
			);
	}

	public function test_no_renderer() {
		$f = $this->getCounterFactory();
		$r = $this->getDefaultRenderer();
		$c = $f->status(1);

		try {
			$r->render($c, $r);	
			$this->assertFalse("We should not get here");
		}
		catch (\LogicException $e) {}
	}
}
