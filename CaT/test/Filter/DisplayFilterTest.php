<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class DisplayFilterTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		$this->type_factory =  new \CaT\Filter\TypeFactory();
		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory(), $this->type_factory);
		$this->gui_factory = new \CaT\Filter\FilterGUIFactory();
	}

	public function test_display_filter() {
		$f = $this->factory;

		$fs = $f->sequence
			( $f->text("l1", "d2")
			, $f->multiselect("l2", "d2", array("a"=>"A","b"=>"B","c"=>"C"))
			, $f->option("l3", "d3")
			, $f->dateperiod("l4", "d4")
			, $f->one_of("l5", "d5"
				, $f->text("l51", "d51")
				, $f->multiselect("l52", "d52", array("a"=>"A","b"=>"B","c"=>"C"))
				, $f->option("l53", "d53")
				, $f->dateperiod("l54", "d54")
				)
			, $f->text("l6", "d6")
			, $f->singleselect("l22", "d22", array("Bernd"=>"A","Karsten"=>"B","Peter"=>"C"))
			);

		$classes = array("catFilterTextGUI", "catFilterMultiselectGUI","catFilterOptionGUI", "catFilterDatePeriodGUI", "catFilterOneOfGUI", "catFilterTextGUI", "catFilterSingleselectGUI");
		$path = array("0", "1", "2", "3", "4", "5", "6");
		$post_values = array();
		$counter = 0;

		$df = new \CaT\Filter\DisplayFilter($this->gui_factory, $this->type_factory);

		while($gui = $df->getNextFilterGUI($fs, $post_values)) {
			$this->assertInstanceOf($classes[$counter], $gui);
			$this->assertEquals($path[$counter], $gui->path());

			$new_path = array($gui->path() => "val");
			$post_values = $new_path + $post_values;
			$counter++;
		}
	}

	public function test_display_filter_more_level() {
		$f = $this->factory;

		$fs = $f->sequence
			( $f->text("l1", "d2")
			, $f->sequence
				( $f->text("l21", "d21")
				, $f->multiselect("l22", "d22", array("a"=>"A","b"=>"B","c"=>"C"))
				, $f->option("l23", "d23")
				, $f->dateperiod("l24", "d24")
				)
			, $f->multiselect("l2", "d2", array("a"=>"A","b"=>"B","c"=>"C"))
			, $f->option("l3", "d3")
			, $f->dateperiod("l4", "d4")
			, $f->one_of("l5", "d5"
				, $f->text("l51", "d51")
				, $f->multiselect("l52", "d52", array("a"=>"A","b"=>"B","c"=>"C"))
				, $f->option("l53", "d53")
				, $f->dateperiod("l54", "d54")
				)
			, $f->text("l6", "d6")
			, $f->singleselect("l22", "d22", array("Bernd"=>"A","Karsten"=>"B","Peter"=>"C"))
			);

		$classes = array("catFilterTextGUI", "catFilterTextGUI", "catFilterMultiselectGUI", "catFilterOptionGUI"
						, "catFilterDatePeriodGUI", "catFilterMultiselectGUI", "catFilterOptionGUI", "catFilterDatePeriodGUI"
						, "catFilterOneOfGUI", "catFilterTextGUI", "catFilterSingleselectGUI");
		$path = array("0","1_0","1_1","1_2","1_3","2","3","4","5","6","7");
		$counter = 0;
		$post_values = array();

		$df = new \CaT\Filter\DisplayFilter($this->gui_factory, $this->type_factory);

		while($gui = $df->getNextFilterGUI($fs, $post_values)) {
			$this->assertInstanceOf($classes[$counter], $gui);
			$this->assertEquals($path[$counter], $gui->path());

			$new_path = array($gui->path() => "val");
			$post_values = $new_path + $post_values;
			$counter++;
		}
	}
}