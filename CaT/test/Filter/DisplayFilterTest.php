<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class DisplayFilterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory(), new \CaT\Filter\TypeFactory());
		$this->gui_factory = new \CaT\Filter\FilterGUIFactory();
	}

	public function test_display_filter() {
		$f1 = $this->factory->text("l1", "d1");
		$f2 = $this->factory->multiselect("l2", "d2", array("a","b","c"));
		$f3 = $this->factory->option("l3", "d3");
		$f4 = $this->factory->dateperiod("l4", "d4");
		$f5 = $this->factory->one_of("l5", "d5", $f1, $f2, $f3, $f4);
		$fs = $this->factory->sequence($f1, $f2, $f3, $f4, $f5);

		$classes = array("catFilterTextGUI", "catFilterMultiselectGUI","catFilterOptionGUI", "catFilterDatePeriodGUI", "catFilterOneOfGUI");
		$path = array("0", "1","2", "3", "4");
		$post_values = array();
		$counter = 0;

		$df = new \CaT\Filter\DisplayFilter($this->gui_factory);

		while($gui = $df->getNextFilterGUI($fs, $post_values)) {
			$this->assertInstanceOf($classes[$counter], $gui);
			$this->assertEquals($path[$counter], $gui->path());

			$post_values[$gui->path()] = "val";
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
		$path = array("0","1:0","1:1","1:2","1:3","2","3","4");
		$counter = 0;
		$post_values = array();

		$df = new \CaT\Filter\DisplayFilter($this->gui_factory);

		while($gui = $df->getNextFilterGUI($fs, $post_values)) {
			$this->assertInstanceOf($classes[$counter], $gui);
			$this->assertEquals($path[$counter], $gui->path());

			$post_values[$gui->path()] = "val";
			$counter++;
		}
	}
}