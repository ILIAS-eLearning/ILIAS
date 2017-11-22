<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class NavigatorTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		$this->factory = new \ILIAS\TMS\Filter\FilterFactory(new \ILIAS\TMS\Filter\PredicateFactory(), new \ILIAS\TMS\Filter\TypeFactory());
	}

	public function test_initial_path() {
		$f = $this->factory->text("l1", "d1");
		$navi = (new \ILIAS\TMS\Filter\Navigator($f));
		$this->assertEquals(null, $navi->path());
	}

	public function test_nav_sequence() {
		$f1 = $this->factory->text("l1", "d1");
		$f2 = $this->factory->text("l2", "d2");
		$f3 = $this->factory->text("l3", "d3");
		$fs = $this->factory->sequence($f1, $f2, $f3);

		$navi = (new \ILIAS\TMS\Filter\Navigator($fs))->go_to("0");
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "0");
		$this->assertEquals($navi->current(), $f1);
		try {
			$navi->up();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}
		try {
			$navi->left();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}
		try {
			$navi->enter();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}

		$navi->right();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "1");
		$this->assertEquals($navi->current(), $f2);
		try {
			$navi->up();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}
		try {
			$navi->enter();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}
		$navi->left();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "0");
		$this->assertEquals($navi->current(), $f1);

		$navi->go_to("2");
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "2");
		$this->assertEquals($navi->current(), $f3);
	}

	public function test_nav_nested_sequence() {
		$f1 = $this->factory->text("l1", "d1");
		$f2 = $this->factory->text("l2", "d2");
		$f3 = $this->factory->text("l3", "d3");

		$f21 = $this->factory->text("l21", "d21");
		$f22 = $this->factory->text("l22", "d22");
		$f23 = $this->factory->text("l23", "d23");
		$fs2 = $this->factory->sequence($f21, $f22, $f23);

		$fs = $this->factory->sequence($f1, $fs2, $f2, $f3);

		$navi = (new \ILIAS\TMS\Filter\Navigator($fs))->go_to("0");
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "0");
		$this->assertEquals($navi->current(), $f1);

		$navi->right();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "1");
		$this->assertEquals($navi->current(), $fs2);

		$navi->enter();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "1_0");
		$this->assertEquals($navi->current(), $f21);
		try {
			$navi->left();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}
		try {
			$navi->enter();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}

		$navi->right();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "1_1");
		$this->assertEquals($navi->current(), $f22);
		try {
			$navi->enter();
			$this->assertFalse("Should have raised.");
		}
		catch (\OutOfBoundsException $e) {}

		$navi->left();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "1_0");
		$this->assertEquals($navi->current(), $f21);

		$navi->up();
		$this->assertEquals($navi->tree(), $fs);
		$this->assertEquals($navi->path(), "1");
		$this->assertEquals($navi->current(), $fs2);
	}
}
