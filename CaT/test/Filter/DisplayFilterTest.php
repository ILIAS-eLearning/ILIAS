<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class DisplayFilterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory());
	}

	public function test_display_filter() {
		$f1 = $this->factory->text();
		$f2 = $this->factory->multiselect();
		$f3 = $this->factory->option();
		$f4 = $this->factory->dateperiod();
		$fs = $this->factory->sequence($f1, $f2, $f3, $f4);

		$classes = array("catFilterTextGUI", "catFilterMultiselectGUI","catFilterOptionGUI", "catFilterDatePeriodGUI");
		$counter = 1;

		$df = new \CaT\Filter\DisplayFilter($fs);

		$gui = $df->getNextFilterGUI(true);
		$this->assertInstanceOf("catFilterTextGUI", $gui);
		
		while($gui = $df->saveFilter()) {
			$this->assertInstanceOf($classes[$counter], $gui);
			$counter++;
		}
	}

	public function test_display_filter_more_level() {
		$f1 = $this->factory->text();
		$f2 = $this->factory->multiselect();
		$f3 = $this->factory->option();
		$f4 = $this->factory->dateperiod();
		$fs = $this->factory->sequence($f1, $f2, $f3, $f4);

		$f21 = $this->factory->text();
		$f22 = $this->factory->multiselect();
		$f23 = $this->factory->option();
		$f24 = $this->factory->dateperiod();
		$fs2 = $this->factory->sequence($f21, $f22, $f23, $f24);

		$fs = $this->factory->sequence($f1, $fs2, $f2, $f3, $f4);

		$classes = array("catFilterTextGUI", "catFilterTextGUI", "catFilterMultiselectGUI", "catFilterOptionGUI"
						, "catFilterDatePeriodGUI", "catFilterMultiselectGUI", "catFilterOptionGUI", "catFilterDatePeriodGUI");
		$counter = 1;

		$df = new \CaT\Filter\DisplayFilter($fs);

		$gui = $df->getNextFilterGUI(true);
		$this->assertInstanceOf("catFilterTextGUI", $gui);
		
		while($gui = $df->saveFilter()) {
			$this->assertInstanceOf($classes[$counter], $gui);
			$counter++;
		}
	}
}