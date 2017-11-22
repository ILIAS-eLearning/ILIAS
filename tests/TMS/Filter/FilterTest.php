<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class FilterTest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;

	protected function setUp()
	{
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		PHPUnit_Framework_Error_Deprecated::$enabled = false;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new \ILIAS\TMS\Filter\FilterFactory(new \ILIAS\TMS\Filter\PredicateFactory(), new \ILIAS\TMS\Filter\TypeFactory());

		// to prevent warnings for unset system timezone
		date_default_timezone_set("Europe/Berlin");
	}

	// DATEPERIOD

	public function test_dateperiod_creation()
	{
		$filter = $this->factory->dateperiod("label", "description");
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $filter->content_type());
		$this->assertEquals($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $filter->input_type());
	}

	public function test_dateperiod_defaults()
	{
		$filter = $this->factory->dateperiod("label", "description");

		$this->assertEquals(new \DateTime(date("Y")."-01-01"), $filter->default_begin());
		$this->assertEquals(new \DateTime(date("Y")."-12-31"), $filter->default_end());
		$this->assertEquals(new \DateTime("1900-01-01"), $filter->period_min());
		// not implemented please uncomment if implemented
		// $this->assertEquals(new \DateTime("2100-12-31"), $filter->period_max());
	}

	public function test_dateperiod_options()
	{
		$filter = $this->factory->dateperiod("label", "description")
			->default_begin(new \DateTime("1990-05-04"))
			->default_end(new \DateTime("2010-05-04"))
			->period_min(new \DateTime("1985-05-04"))
			;
			// not implemented please uncomment if implemented
			// ->period_max(new \DateTime("2015-05-04"))
			// ;

		$this->assertEquals(new \DateTime("1990-05-04"), $filter->default_begin());
		$this->assertEquals(new \DateTime("2010-05-04"), $filter->default_end());
		$this->assertEquals(new \DateTime("1985-05-04"), $filter->period_min());
		// not implemented please uncomment if implemented
		// $this->assertEquals(new \DateTime("2015-05-04"), $filter->period_max());
	}

	public function test_dateperiod_overlaps_predicate()
	{
		$filter = $this->factory->dateperiod("label", "description")
			->map_to_predicate($this->factory->dateperiod_overlaps_predicate("start_field", "end_field"))
			;
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals($tf->cls("\\ILIAS\\TMS\\Filter\\Predicates\\Predicate"), $filter->content_type());
		$this->assertEquals($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $filter->input_type());

		$predicate = $filter->content(new \DateTime("2000-01-01"), new \DateTime("2000-12-31"));

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Predicates\\Predicate", $predicate);

		$fields = array_map(
			function (\ILIAS\TMS\Filter\Predicates\Field $f) {
					return $f->name();
			},
			$predicate->fields()
		);
		$this->assertEquals(array("start_field", "end_field"), $fields);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$this->assertTrue($interpreter->interpret(
			$predicate,
			array
					( "start_field"	=> new \DateTime("1999-05-04")
					, "end_field"	=> new \DateTime("2000-05-04")
					)
		));

		$this->assertTrue($interpreter->interpret(
			$predicate,
			array
					( "start_field"	=> new \DateTime("1999-05-04")
					, "end_field"	=> new \DateTime("2001-05-04")
					)
		));

		$this->assertTrue($interpreter->interpret(
			$predicate,
			array
					( "start_field"	=> new \DateTime("2000-05-04")
					, "end_field"	=> new \DateTime("2001-05-04")
					)
		));

		$this->assertFalse($interpreter->interpret(
			$predicate,
			array
					( "start_field"	=> new \DateTime("2001-05-04")
					, "end_field"	=> new \DateTime("2002-05-04")
					)
		));

		$this->assertFalse($interpreter->interpret(
			$predicate,
			array
					( "start_field"	=> new \DateTime("1998-05-04")
					, "end_field"	=> new \DateTime("1999-05-04")
					)
		));
	}

	// OPTIONS

	public function test_option_creation()
	{
		$filter = $this->factory->option("label", "description");
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals($tf->bool(), $filter->content_type());
		$this->assertEquals($tf->bool(), $filter->input_type());
	}

	public function test_option_predicate()
	{
		$filter = $this->factory->option("label", "description")
			->map_to_predicate(function ($bool) {
				$f = $this->factory->predicate_factory();
				if ($bool) {
					return $f->field("foo")->LE()->int(3);
				} else {
					return $f->field("foo")->EQ()->int(4);
				}
			});

		$this->assertNotNull($filter);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$pred_true = $filter->content(true);

		$this->assertTrue($interpreter->interpret($pred_true, array("foo" => 2)));
		$this->assertFalse($interpreter->interpret($pred_true, array("foo" => 4)));

		$pred_false = $filter->content(false);

		$this->assertFalse($interpreter->interpret($pred_false, array("foo" => 2)));
		$this->assertTrue($interpreter->interpret($pred_false, array("foo" => 4)));
	}

	public function test_option_checked()
	{
		$filter = $this->factory->option("label", "description");
		$this->assertFalse($filter->clone_with_checked(false)->getChecked());
		$this->assertTrue($filter->clone_with_checked(true)->getChecked());
	}

	// MULTISELECT

	public function test_multiselection_creation()
	{
		$options = array
			( 1 => "one"
			, 2 => "two"
			, 3 => "three"
			);

		$filter = $this->factory->multiselect("label", "description", $options);
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals($tf->lst($tf->int()), $filter->content_type());
		$this->assertEquals($tf->lst($tf->int()), $filter->input_type());
		$this->assertEquals($options, $filter->options());
	}

	public function test_multiselect_content_type_string()
	{
		$options = array
			( "foo" => "bar"
			);
		$tf = $this->factory->type_factory();

		$filter = $this->factory->multiselect("label", "description", $options);
		$this->assertEquals($tf->lst($tf->string()), $filter->content_type());
		$this->assertEquals($tf->lst($tf->string()), $filter->input_type());
	}

	/**
	 * @dataProvider	invalid_key_types_for_multiselect_provider
	 */
	/*public function test_multiselect_invalid_key_types($key) {
		try {
			$this->factory->multiselect("l", "d", array($key => "foobar"));
			$this->assertFalse("Should have raised.");
		}
		catch (\InvalidArgumentException $e) {
		}
	}*/

	public function invalid_key_types_for_multiselect_provider()
	{
		//php casts float to ints and false or true to 0 or 1
		//test running without throwing error
		return array
			( //array(1.2)
			//, array(true)
			);
	}

	public function test_multiselect_options()
	{
		$options = array
			( 1 => "one"
			, 2 => "two"
			, 3 => "three"
			);

		$filter = $this->factory->multiselect("label", "description", $options)
			->default_choice(array(1,3))
			;

		$this->assertEquals(array(1,3), $filter->default_choice());
	}

	public function test_multiselect_predicate()
	{
		$options = array
			( 1 => "one"
			, 2 => "two"
			, 3 => "three"
			);

		$filter = $this->factory->multiselect("label", "description", $options)
			->map_to_predicate(function (array $options) {
				$f = $this->factory->predicate_factory();
				return $f->field("foo")->IN(call_user_func_array(array($f, "list_int"), $options));
			});

		$this->assertNotNull($filter);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$pred = $filter->content(array(1,2));
		$this->assertTrue($interpreter->interpret($pred, array("foo" => 2)));
		$this->assertFalse($interpreter->interpret($pred, array("foo" => 3)));

		$pred = $filter->content(array(3));
		$this->assertFalse($interpreter->interpret($pred, array("foo" => 2)));
		$this->assertTrue($interpreter->interpret($pred, array("foo" => 3)));
	}

	//SINGLESELECT
	public function test_singleselection_creation()
	{
		$options = array
			( 1 => "one"
			, 2 => "two"
			, 3 => "three"
			);

		$filter = $this->factory->singleselect("label", "description", $options);
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals($tf->int(), $filter->content_type());
		$this->assertEquals($tf->int(), $filter->input_type());
		$this->assertEquals($options, $filter->options());
	}

	public function test_singleselect_content_type_string()
	{
		$options = array
			( "foo" => "bar"
			);
		$tf = $this->factory->type_factory();

		$filter = $this->factory->singleselect("label", "description", $options);
		$this->assertEquals($tf->string(), $filter->content_type());
		$this->assertEquals($tf->string(), $filter->input_type());
	}

	public function test_singleselectselect_options()
	{
		$options = array
			( 1 => "one"
			, 2 => "two"
			, 3 => "three"
			);

		$filter = $this->factory->singleselect("label", "description", $options)
			->default_choice(1)
			;

		$this->assertEquals(1, $filter->default_choice());
	}

	// TEXT

	public function test_text_creation()
	{
		$filter = $this->factory->text("label", "description");
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("label", $filter->label());
		$this->assertEquals("description", $filter->description());
		$this->assertEquals($tf->string(), $filter->content_type());
		$this->assertEquals($tf->string(), $filter->input_type());
	}

	public function test_text_predicate()
	{
		$filter = $this->factory->text("label", "description")
			->map_to_predicate(function ($str) {
				$f = $this->factory->predicate_factory();
				return $f->field("foo")->EQ()->str($str);
			});

		$this->assertNotNull($filter);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$pred_true = $filter->content("bar");

		$this->assertTrue($interpreter->interpret($pred_true, array("foo" => "bar")));
		$this->assertFalse($interpreter->interpret($pred_true, array("foo" => "foo")));
	}

	// COMBINATORS

	// SEQUENCE

	public function test_sequence_filters_creation()
	{
		$dt_f = $this->factory->dateperiod("label", "description");
		$text1_f = $this->factory->text("label", "description");
		$text2_f = $this->factory->text("label", "description");
		$filter = $this->factory->sequence($dt_f, $text1_f, $text2_f);
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals(null, $filter->label());
		$this->assertEquals(null, $filter->description());
		$this->assertEquals($tf->tuple($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $tf->string(), $tf->string()), $filter->content_type());
		$this->assertEquals($tf->tuple($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $tf->string(), $tf->string()), $filter->input_type());
	}

	public function test_sequence_filters_predicate()
	{
		$dt_f = $this->factory->dateperiod("label", "description");
		$text1_f = $this->factory->text("label", "description");
		$text2_f = $this->factory->text("label", "description");
		$filter = $this->factory->sequence($dt_f, $text1_f, $text2_f)
			->map_to_predicate(function ($dt_l, $dt_r, $text1, $text2) {
				$f = $this->factory->predicate_factory();
				return    $f->field("dt_l")->EQ()->date($dt_l)
					// TODO: make this fluent again.
					->_AND($f->field("dt_r")->EQ()->date($dt_r))
					->_AND($f->field("text1")->EQ()->str($text1))
					->_AND($f->field("text2")->EQ()->str($text2));
			});

		$this->assertNotNull($filter);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$pred = $filter->content(new \DateTime("1985-05-04"), new \DateTime("2015-05-04"), "foo", "bar");

		$this->assertTrue($interpreter->interpret($pred, array
							( "dt_l"	=> new \DateTime("1985-05-04")
							, "dt_r"	=> new \DateTime("2015-05-04")
							, "text1"	=> "foo"
							, "text2"	=> "bar"
							)));
		$this->assertFalse($interpreter->interpret($pred, array
							( "dt_l"	=> new \DateTime("1985-05-04")
							, "dt_r"	=> new \DateTime("2015-05-04")
							, "text1"	=> "foobar"
							, "text2"	=> "bar"
							)));
	}

	public function test_sequence_and_filters_predicate()
	{
		$f = $this->factory;

		$dt_f = $f->dateperiod("label", "description")->map_to_predicate(
			$f->dateperiod_overlaps_predicate("dt_l", "dt_r")
		);
		$text1_f = $f->text("label", "description")->map_to_predicate(
			$f->text_equals("text1")
		);
		$text2_f = $f->text("label", "description")->map_to_predicate(
			$f->text_equals("text2")
		);

		$filter = $f->sequence_and($dt_f, $text1_f, $text2_f);

		$this->assertNotNull($filter);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$pred = $filter->content(new \DateTime("1985-05-04"), new \DateTime("2015-05-04"), "foo", "bar");

		$this->assertTrue($interpreter->interpret($pred, array
							( "dt_l"	=> new \DateTime("1985-05-04")
							, "dt_r"	=> new \DateTime("2015-05-04")
							, "text1"	=> "foo"
							, "text2"	=> "bar"
							)));
		$this->assertFalse($interpreter->interpret($pred, array
							( "dt_l"	=> new \DateTime("1985-05-04")
							, "dt_r"	=> new \DateTime("2015-05-04")
							, "text1"	=> "foobar"
							, "text2"	=> "bar"
							)));
	}

	// OPTIONS

	public function test_one_of_filter_creation()
	{
		$dt_f = $this->factory->dateperiod("label", "description");
		$text_f = $this->factory->text("label", "description");
		$filter = $this->factory->one_of("oolabel", "oodescription", $dt_f, $text_f);
		$tf = $this->factory->type_factory();

		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Filters\\Filter", $filter);
		$this->assertEquals("oolabel", $filter->label());
		$this->assertEquals("oodescription", $filter->description());
		$this->assertEquals($tf->option($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $tf->string()), $filter->content_type());
		$this->assertEquals($tf->option($tf->tuple($tf->cls("\\DateTime"), $tf->cls("\\DateTime")), $tf->string()), $filter->input_type());
	}

	public function test_one_of_filter_predicate()
	{
		$dt_f = $this->factory->dateperiod("label", "description");
		$text_f = $this->factory->text("label", "description");
		$filter = $this->factory->one_of("oolabel", "oodescription", $dt_f, $text_f)
					->map_to_predicate(function ($choice, $vals) {
						$f = $this->factory->predicate_factory();

						$this->assertInternalType("int", $choice);
						$this->assertContains($choice, array(0,1));

						if ($choice === 0) {
							list($dt_l, $dt_r) = $vals;
							$this->assertInstanceOf("\\DateTime", $dt_l);
							$this->assertInstanceOf("\\DateTime", $dt_r);
						} else {
							list($text) = $vals;
							$this->assertInternalType("string", $text);
						}
						return $f->field("choice")->EQ()->int($choice);
					});

		$this->assertNotNull($filter);

		$interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter;

		$pred_0 = $filter->content(0, array(new \DateTime("1985-05-04"), new \DateTime("2015-05-04")));

		$this->assertTrue($interpreter->interpret($pred_0, array("choice" => 0)));
		$this->assertFalse($interpreter->interpret($pred_0, array("choice" => 1)));

		$pred_1 = $filter->content(0, array(new \DateTime("1985-05-04"), new \DateTime("2015-05-04")));

		$this->assertTrue($interpreter->interpret($pred_1, array("choice" => 0)));
		$this->assertFalse($interpreter->interpret($pred_1, array("choice" => 1)));
	}
}
