<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class FilterTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new \CaT\Filter\FilterFactory(new \CaT\Filter\PredicateFactory());
	}

	// DATEPERIOD

	public function test_dateperiod_creation() {
		$filter = $this->factory->dateperiod("label", "description");

		$this->assertInstanceOf("\\CaT\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals(array("\\DateTime", "\\DateTime"), $filter->content_type());
		$this->assertEquals(array("\\DateTime", "\\DateTime"), $filter->input_type());
	}

	public function test_dateperiod_defaults() {
		// to prevent warnings for unset system timezone
		date_default_timezone_set("Europe/Berlin");

		$filter = $this->factory->dateperiod("label", "description");

		$this->assertEquals(new \DateTime(date("Y")."-01-01"), $filter->default_begin());
		$this->assertEquals(new \DateTime(date("Y")."-12-01"), $filter->default_end());
		$this->assertEquals(new \DateTime("1900-01-01"), $filter->period_min());
		$this->assertEquals(new \DateTime("2100-12-31"), $filter->period_max());
	}

	public function test_dateperiod_options() {
		$filter = $this->factory->dateperiod("label", "description")
			->default_begin(new \DateTime("1990-05-04"))
			->default_end(new \DateTime("2010-05-04"))
			->period_min(new \DateTime("1985-05-04"))
			->period_max(new \DateTime("2015-05-04"))
			;

		$this->assertEquals(new \DateTime("1990-05-04"), $filter->default_begin());
		$this->assertEquals(new \DateTime("2010-05-04"), $filter->default_end());
		$this->assertEquals(new \DateTime("1985-05-04"), $filter->period_min());
		$this->assertEquals(new \DateTime("2015-05-04"), $filter->period_max());
	}

	public function test_dateperiod_overlaps_predicate() {
		$filter = $this->factory->dateperiod("label", "description")
			->map_to_predicate($this->factory->dateperiod_overlaps_predicate("start_field", "end_field"))
			;

		$this->assertInstanceOf("\\CaT\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals(array("\\CaT\\Filter\\Predicates\\Predicate"), $filter->content_type());
		$this->assertEquals(array("\\DateTime", "\\DateTime"), $filter->input_type());

		$predicate = $filter->content(new \DateTime("2000-01-01"), new \DateTime("2000-12-31"));

		$this->assertInstanceOf("\\CaT\\Filter\\Predicates\\Predicate", $predicate);
		
		$fields = array_map
			( function(\CaT\Filter\Predicates\Field $f) { return $f->name(); }
			, $predicate->fields()
			);
		$this->assertEquals(array("start_field", "end_field"), $fields);

		$interpreter = new \CaT\Filter\DictionaryPredicateInterpreter;

		$this->assertTrue($interpreter->interpret
				( $predicate
				, array
					( "start_field"	=> new \DateTime("1999-05-04")
					, "end_field"	=> new \DateTime("2000-05-04")
					)
				));

		$this->assertTrue($interpreter->interpret
				( $predicate
				, array
					( "start_field"	=> new \DateTime("1999-05-04")
					, "end_field"	=> new \DateTime("2001-05-04")
					)
				));

		$this->assertTrue($interpreter->interpret
				( $predicate
				, array
					( "start_field"	=> new \DateTime("2000-05-04")
					, "end_field"	=> new \DateTime("2001-05-04")
					)
				));

		$this->assertFalse($interpreter->interpret
				( $predicate
				, array
					( "start_field"	=> new \DateTime("2001-05-04")
					, "end_field"	=> new \DateTime("2002-05-04")
					)
				));

		$this->assertFalse($interpreter->interpret
				( $predicate
				, array
					( "start_field"	=> new \DateTime("1998-05-04")
					, "end_field"	=> new \DateTime("1999-05-04")
					)
				));
	}

	// OPTIONS

	public function test_option_creation() {
		$filter = $this->factory->option("label", "description");

		$this->assertInstanceOf("\\CaT\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals(array("bool"), $filter->content_type());
		$this->assertEquals(array("bool"), $filter->input_type());
	}

	public function test_option_predicate() {
		$filter = $this->factory->option("label", "description")
			->map_to_predicate(function($bool) {
				$f = $this->factory->predicate_factory();
				if ($bool) {
					return $f->field("foo")->LT()->int(3);
				}
				else {
					return $f->field("foo")->GE()->int(3);
				}
			});

		$interpreter = new \CaT\Filter\DictionaryPredicateInterpreter;

		$pred_true = $filter->content(true);

		$this->assertTrue($interpreter->interpret($pred_true, array("foo" => 2)));
		$this->assertFalse($interpreter->interpret($pred_true, array("foo" => 4)));

		$pred_false = $filter->content(false);

		$this->assertFalse($interpreter->interpret($pred_false, array("foo" => 2)));
		$this->assertTrue($interpreter->interpret($pred_false, array("foo" => 4)));
	}

	// MULTISELECT

	public function test_multiselection_creation() {
		$options = array
			( 1 => "one"
			, 2 => "two"
			, 3 => "three"
			);

		$filter = $this->factory->multiselect("label", "description", $options);

		$this->assertInstanceOf("\\CaT\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals($options, $filter->options());
	}

	public function test_text_creation() {
		$filter = $this->factory->text("label", "description");

		$this->assertInstanceOf("\\CaT\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
	}
}