<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class DisplayFilterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory());
	}

	public function test_display_filter() {
		$f1 = $this->factory->text();
		$f2 = $this->factory->text();
		$f3 = $this->factory->text();
		$fs = $this->factory->sequence($f1, $f2, $f3);

		$df = new \CaT\Filter\DisplayFilter($fs);

		$gui = $df->getNextFilterGUI(true);
		$this->assertInstanceOf("catFilterTextGUI", $gui);
		$gui = $df->saveFilter();

		$this->assertInstanceOf("catFilterTextGUI", $gui);
		$gui = $df->saveFilter();

		$this->assertInstanceOf("catFilterTextGUI", $gui);
		$gui = $df->saveFilter();
	}

	public function test_display_filter_more_level() {
		$f1 = $this->factory->text();
		$f2 = $this->factory->text();
		$f3 = $this->factory->text();

		$f21 = $this->factory->text();
		$f22 = $this->factory->text();
		$f23 = $this->factory->text();
		$fs2 = $this->factory->sequence($f21, $f22, $f23);

		$fs = $this->factory->sequence($f1, $fs2, $f2, $f3);

	}
}