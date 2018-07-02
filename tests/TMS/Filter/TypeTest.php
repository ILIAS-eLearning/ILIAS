<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class FilterTypeTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new \ILIAS\TMS\Filter\TypeFactory();

		// to prevent warnings for unset system timezone
		date_default_timezone_set("Europe/Berlin");
	}

	/**
	 * @dataProvider	int_provider
	 */
	public function test_int($maybe_int, $res) {
		$int_type = $this->factory->int();
		$this->assertSame($int_type->contains($maybe_int), $res);
	}

	/**
	 * @dataProvider	string_provider
	 */
	public function test_string($maybe_string, $res) {
		$string_type = $this->factory->string();
		$this->assertSame($string_type->contains($maybe_string), $res);
	}

	/**
	 * @dataProvider	bool_provider
	 */
	public function test_bool($maybe_bool, $res) {
		$bool_type = $this->factory->bool();
		$this->assertSame($bool_type->contains($maybe_bool), $res);
	}

	/**
	 * @dataProvider	class_provider
	 */
	public function test_class($cls_name, $maybe_obj, $res) {
		$class_type = $this->factory->cls($cls_name);
		$this->assertSame($class_type->contains($maybe_obj), $res);
	}

	/**
	 * @dataProvider	tuple_provider
	 */
	public function test_tuple($type_factory, $maybe_tup, $res) {
		$tup_type = $type_factory($this->factory);
		$this->assertSame($tup_type->contains($maybe_tup), $res);
	}

	/**
	 * @dataProvider	dict_provider
	 */
	public function test_dict($type_factory, $maybe_dict, $res) {
		$dict_type = $type_factory($this->factory);
		$this->assertSame($dict_type->contains($maybe_dict), $res);
	}


	/**
	 * @dataProvider	either_provider
	 */
	public function test_either($type_factory, $maybe_either, $res) {
		$either_type = $type_factory($this->factory);
		$this->assertSame($either_type->contains($maybe_either), $res);
	}

	/**
	 * @dataProvider	list_provider
	 */
	public function test_list($type_factory, $maybe_list, $res) {
		$list_type = $type_factory($this->factory);
		$this->assertSame($list_type->contains($maybe_list), $res);
	}

	/**
	 * @dataProvider	option_provider
	 */
	public function test_option($type_factory, $maybe_option, $res) {
		$option_type = $type_factory($this->factory);
		$this->assertSame($option_type->contains($maybe_option), $res);
	}

	/**
	 * @dataProvider	unflatten_provider
	 */
	public function test_unflatten($type_factory, $flattened, $unflattened) {
		$type = $type_factory($this->factory);
		$this->assertEquals($type->unflatten($flattened), $unflattened);
	}

	public function int_provider() {
		return array
			( array(42, true)
			, array(0, true)
			, array(-23, true)
			, array(null, false)
			, array("3", false)
			, array(false, false)
			, array(true, false)
			, array(array(), false)
			, array(new \stdClass(), false)
			);
	}

	public function string_provider() {
		return array
			( array("", true)
			, array("abcdef", true)
			, array(42, true)
			, array(null, false)
			, array(false, false)
			, array(true, false)
			, array(array(), false)
			, array(new \stdClass(), false)
			);
	}

	public function bool_provider() {
		// to prevent warnings for unset system timezone
		date_default_timezone_set("Europe/Berlin");

		return array
			( array(false, true)
			, array(true, true)
			, array(new \stdClass(), false)
			, array(new \DateTime("1985-05-04"), false)
			, array("", false)
			, array(null, false)
			, array(42, false)
			, array(array(), false)
			);
	}

	public function class_provider() {
		// to prevent warnings for unset system timezone
		date_default_timezone_set("Europe/Berlin");

		return array
			( array("\\stdClass", new \stdClass(), true)
			, array("\\DateTime", new \DateTime("1985-05-04"), true)
			, array("\\stdClass", "", false)
			, array("\\stdClass", null, false)
			, array("\\stdClass", 42, false)
			, array("\\stdClass", false, false)
			, array("\\stdClass", true, false)
			, array("\\stdClass", array(), false)
			);
	}

	public function tuple_provider() {
		return array
			( array(function($f) { return $f->tuple($f->cls("\\stdClass")); }, new \stdClass(), false)
			, array(function($f) { return $f->tuple($f->string()); }, "", false)
			, array(function($f) { return $f->tuple($f->string()); }, null, false)
			, array(function($f) { return $f->tuple($f->int()); }, 42, false)
			, array(function($f) { return $f->tuple($f->int()); }, array(), false)
			, array(function($f) { return $f->tuple($f->int()); }, array(1), true)
			, array(function($f) { return $f->tuple($f->int()); }, array("1"), false)
			, array(function($f) { return $f->tuple($f->int(), $f->int()); }, array(1,2), true)
			, array(function($f) { return $f->tuple($f->int(), $f->int()); }, array(1,"2"), false)
			, array(function($f) { return $f->tuple(); }, array(), true)
			);
	}

	public function dict_provider() {
		return array
			( array(function($f) { return $f->dict( array("cls" => $f->cls("\\stdClass")) ); }, array("cls2" => new \stdClass()), false)
			, array(function($f) { return $f->dict( array("cls" => $f->cls("\\stdClass")) ); }, array("cls" => new \stdClass()), true)
			, array(function($f) { return $f->dict(array("str" => $f->string())); }, null, false)
			, array(function($f) { return $f->dict(array("int" => $f->int())); }, array(42), false)
			, array(function($f) { return $f->dict(array("int" => $f->int())); }, array("int" => 42), true)
			, array(function($f) { return $f->dict(array($f->int())); }, array(1), true)
			, array(function($f) { return $f->dict(array("int" => $f->int())); }, array("int" => "1"), false)
			, array(function($f) { return $f->dict(array("int1" => $f->int(), "int2" => $f->int())); }, array("int2" => 1, "int1" => 2), true)
			, array(function($f) { return $f->dict(array($f->int(), $f->int())); }, array(1,"2"), false)
			);
	}


	public function either_provider() {
		return array
			( array(function($f) { return $f->either($f->cls("\\stdClass")); }, new \stdClass(), true)
			, array(function($f) { return $f->either($f->string()); }, "", true)
			, array(function($f) { return $f->either($f->string()); }, null, false)
			, array(function($f) { return $f->either($f->int(), $f->string()); }, 1, true)
			, array(function($f) { return $f->either($f->int(), $f->string()); }, "2", true)
			, array(function($f) { return $f->either($f->int(), $f->string()); }, array("2"), false)
			);
	}

	public function list_provider() {
		return array
			( array(function($f) { return $f->lst($f->cls("\\stdClass")); }, new \stdClass(), false)
			, array(function($f) { return $f->lst($f->cls("\\stdClass")); }, array(), true)
			, array(function($f) { return $f->lst($f->cls("\\stdClass")); }, array(new \stdClass()), true)
			, array(function($f) { return $f->lst($f->string()); }, "", false)
			, array(function($f) { return $f->lst($f->string()); }, array(""), true)
			, array(function($f) { return $f->lst($f->string()); }, array("", "11"), true)
			, array(function($f) { return $f->lst($f->string()); }, array("", 12), true)
			);
	}

	public function option_provider() {
		return array
			( array(function($f) { return $f->option($f->cls("\\stdClass")); }, array(0, new \stdClass()), true)
			, array(function($f) { return $f->option($f->cls("\\stdClass")); }, array(1, new \stdClass()), false)
			, array(function($f) { return $f->option($f->cls("\\stdClass")); }, new \stdClass(), false)
			, array(function($f) { return $f->option($f->int(), $f->string()); }, array(0, 1), true)
			, array(function($f) { return $f->option($f->int(), $f->string()); }, array(1, "23"), true)
			, array(function($f) { return $f->option($f->int(), $f->string()); }, array(1, 234), true)
			, array(function($f) { return $f->option($f->int(), $f->string()); }, array(0, "23"), false)
			, array(function($f) { return $f->option($f->int(), $f->string()); }, array(2, 23), false)
			);
	}

	public function unflatten_provider() {
		return array
			( array(function($f) { return $f->int(); }, array(1), 1)
			, array(function($f) { return $f->string(); }, array("a"), "a")
			, array(function($f) { return $f->tuple($f->string(), $f->int()); }, array("a", 1), array("a", 1))
			, array(function($f) { return $f->tuple($f->string(), $f->int()); }, array("a", 1), array("a", 1))
			, array(function($f) { return $f->tuple($f->string(), $f->tuple($f->int(), $f->int())); }, array("a", 1, 2), array("a", array(1,2)))
			, array(function($f) { return $f->option($f->string(), $f->int()); }, array(0, "1"), array(0, "1"))
			, array(function($f) { return $f->option($f->string(), $f->int()); }, array(1, 2), array(1, 2))
			, array(function($f) { return $f->dict( array("str" => $f->string(), "tup" => $f->tuple($f->string(), $f->int())) , true); }, array("a", "b", 1), array("str" => "a", "tup" => array("b", 1)))
			);
	}
}
