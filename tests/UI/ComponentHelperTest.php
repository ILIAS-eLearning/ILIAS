<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

class ComponentMock {
	use \ILIAS\UI\Implementation\Component\ComponentHelper;

	public function _checkArg($which, $check, $message) {
		$this->checkArg($which, $check, $message);
	}
	public function _checkIntArg($which, $value) {
		$this->checkIntArg($which, $value);
	}
	public function _checkStringArg($which, $value) {
		$this->checkStringArg($which, $value);
	}
	public function _checkArgIsElement($which, $value, $array, $name) {
		$this->checkArgIsElement($which, $value, $array, $name);
	}
}

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class ComponentHelperTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->mock = new ComponentMock();
	}

	public function test_check_arg_ok() {
		try {
			$this->mock->_checkArg("some_arg", true, "some message");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertFalse("This should not happen.");
		}
	}

	public function test_check_arg_not_ok() {
		try {
			$this->mock->_checkArg("some_arg", false, "some message");
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertEquals("Argument 'some_arg': some message", $e->getMessage());
		}
	}

	public function test_check_int_arg_ok() {
		try {
			$this->mock->_checkIntArg("some_arg", 1); 
		}
		catch (\InvalidArgumentException $e) {
			$this->assertFalse("This should not happen.");
		}
	}

	public function test_check_int_arg_not_ok() {
		try {
			$this->mock->_checkIntArg("some_arg", "foo");
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertEquals("Argument 'some_arg': expected integer, got string 'foo'", $e->getMessage());
		}
	}

	public function test_check_string_arg_ok() {
		try {
			$this->mock->_checkStringArg("some_arg", "bar"); 
		}
		catch (\InvalidArgumentException $e) {
			$this->assertFalse("This should not happen.");
		}
	}

	public function test_check_string_arg_not_ok() {
		try {
			$this->mock->_checkStringArg("some_arg", 1);
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertEquals("Argument 'some_arg': expected string, got integer '1'", $e->getMessage());
		}
	}

	public function test_check_arg_is_element_ok() {
		try {
			$this->mock->_checkArgIsElement("some_arg", "bar", array("foo", "bar"), "foobar"); 
		}
		catch (\InvalidArgumentException $e) {
			$this->assertFalse("This should not happen.");
		}
	}

	public function test_check_string_arg_is_element_not_ok() {
		try {
			$this->mock->_checkArgIsElement("some_arg", "baz", array("foo", "bar"), "foobar"); 
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertEquals("Argument 'some_arg': expected foobar, got 'baz'", $e->getMessage());
		}
	}
}
