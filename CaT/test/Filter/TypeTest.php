<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class TypeTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new \CaT\Filter\TypeFactory();

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
	 * @dataProvider	either_provider
	 */
	public function test_either($type_factory, $maybe_either, $res) {
		$either_type = $type_factory($this->factory);
		$this->assertSame($either_type->contains($maybe_either), $res);
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
			, array(42, false)
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
}