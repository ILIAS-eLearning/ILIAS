<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class DisplayFilterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		error_reporting(E_ALL);
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory(), new \CaT\Filter\TypeFactory());
	}

	public function test_display_filter() {
		$f1 = $this->factory->text("l1", "d1");
		$f2 = $this->factory->multiselect("l2", "d2", array("a","b","c"));
		$f3 = $this->factory->option("l3", "d3");
		$f4 = $this->factory->dateperiod("l4", "d4");
		$f5 = $this->factory->one_of("l5", "d5", $f1, $f2, $f3, $f4);
		$fs = $this->factory->sequence($f1, $f2, $f3, $f4, $f5);

		$classes = array("catFilterTextGUI", "catFilterMultiselectGUI","catFilterOptionGUI", "catFilterDatePeriodGUI", "catFilterOneOfGUI");
		$counter = 0;
		$start_first_filter = true;

		$df = new \CaT\Filter\DisplayFilter($fs, $this);

		while($gui = $df->getNextFilterGUI($start_first_filter)) {
			$start_first_filter = false;
			try {
				$df->saveFilter();
			} catch( Exception $e) {}
			$this->assertInstanceOf($classes[$counter], $gui);
			$counter++;
		}
	}

	public function test_display_filter_more_level() {
		$f1 = $this->factory->text("l1", "d1");
		$f2 = $this->factory->multiselect("l2", "d2", array("a","b","c"));
		$f3 = $this->factory->option("l3", "d3");
		$f4 = $this->factory->dateperiod("l4", "d4");
		$f5 = $this->factory->one_of("l5", "d5", $f1, $f2, $f3, $f4);
		$fs = $this->factory->sequence($f1, $f2, $f3, $f4, $f5);

		$f21 = $this->factory->text("l1", "d1");
		$f22 = $this->factory->multiselect("l2", "d2", array("a","b","c"));
		$f23 = $this->factory->option("l3", "d3");
		$f24 = $this->factory->dateperiod("l4", "d4");
		$fs2 = $this->factory->sequence($f21, $f22, $f23, $f24);

		$fs = $this->factory->sequence($f1, $fs2, $f2, $f3, $f4);

		$classes = array("catFilterTextGUI", "catFilterTextGUI", "catFilterMultiselectGUI", "catFilterOptionGUI"
						, "catFilterDatePeriodGUI", "catFilterMultiselectGUI", "catFilterOptionGUI", "catFilterDatePeriodGUI", "catFilterOneOfGUI");
		$counter = 0;
		$start_first_filter = true;

		$df = new \CaT\Filter\DisplayFilter($fs, $this);

		while($gui = $df->getNextFilterGUI($start_first_filter)) {
			$start_first_filter = false;
			try {
				$df->saveFilter();
			} catch( Exception $e) {}
			$this->assertInstanceOf($classes[$counter], $gui);
			$counter++;
		}
	}
}