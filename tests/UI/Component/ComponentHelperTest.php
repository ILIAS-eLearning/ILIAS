<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

require_once("libs/composer/vendor/autoload.php");

require_once(__DIR__ . "/../Renderer/TestComponent.php");

class ComponentMock
{
    use \ILIAS\UI\Implementation\Component\ComponentHelper;

    public function _checkArg($which, $check, $message)
    {
        $this->checkArg($which, $check, $message);
    }
    public function _checkIntArg($which, $value)
    {
        $this->checkIntArg($which, $value);
    }
    public function _checkStringArg($which, $value)
    {
        $this->checkStringArg($which, $value);
    }
    public function _checkFloatArg($which, $value)
    {
        $this->checkFloatArg($which, $value);
    }
    public function _checkBoolArg($which, $value)
    {
        $this->checkBoolArg($which, $value);
    }
    public function _checkArgInstanceOf($which, $value, $class)
    {
        $this->checkArgInstanceOf($which, $value, $class);
    }
    public function _checkArgIsElement($which, $value, $array, $name)
    {
        $this->checkArgIsElement($which, $value, $array, $name);
    }
    public function _toArray($value)
    {
        return $this->toArray($value);
    }
    public function _checkArgListElements($which, &$value, $classes)
    {
        $this->checkArgListElements($which, $value, $classes);
    }
    public function _checkArgList($which, &$value, $check, $message)
    {
        $this->checkArgList($which, $value, $check, $message);
    }

    public $called_gcnbfqn = 0;
    protected function getCanonicalNameByFullyQualifiedName()
    {
        $this->called_gcnbfqn++;
        return "Foo";
    }
}

class Class1
{
}
class Class2
{
}
class Class3
{
}

/**
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class ComponentHelperTest extends TestCase
{
    public function setUp() : void
    {
        $this->mock = new ComponentMock();
    }

    public function test_getCanonicalName()
    {
        $c = new \ILIAS\UI\Component\Test\TestComponent("foo");
        $this->assertEquals("Test Component Test", $c->getCanonicalName());
    }

    public function test_cachesCanonicalName()
    {
        $name1 = $this->mock->getCanonicalName();
        $name2 = $this->mock->getCanonicalName();
        $this->assertEquals($name1, $name2);
        $this->assertEquals(1, $this->mock->called_gcnbfqn);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_ok()
    {
        $this->mock->_checkArg("some_arg", true, "some message");
    }

    public function test_check_arg_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': some message");
        $this->mock->_checkArg("some_arg", false, "some message");
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_int_arg_ok()
    {
        $this->mock->_checkIntArg("some_arg", 1);
    }

    public function test_check_int_arg_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected integer, got string 'foo'");
        $this->mock->_checkIntArg("some_arg", "foo");
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_string_arg_ok()
    {
        $this->mock->_checkStringArg("some_arg", "bar");
    }

    public function test_check_string_arg_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected string, got integer '1'");
        $this->mock->_checkStringArg("some_arg", 1);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_bool_arg_ok()
    {
        $this->mock->_checkBoolArg("some_arg", true);
    }

    public function test_check_bool_arg_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected bool, got integer '1'");
        $this->mock->_checkBoolArg("some_arg", 1);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_instanceof_ok()
    {
        $this->mock->_checkArgInstanceOf("some_arg", $this->mock, ComponentMock::class);
    }

    public function test_check_arg_instanceof_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected ComponentMock, got ComponentHelperTest");
        $this->mock->_checkArgInstanceOf("some_arg", $this, ComponentMock::class);
    }


    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_is_element_ok()
    {
        $this->mock->_checkArgIsElement("some_arg", "bar", array("foo", "bar"), "foobar");
    }

    public function test_check_string_arg_is_element_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected foobar, got 'baz'");
        $this->mock->_checkArgIsElement("some_arg", "baz", array("foo", "bar"), "foobar");
    }

    public function test_to_array_with_array()
    {
        $foo = array("foo", "bar");
        $res = $this->mock->_toArray($foo);

        $this->assertEquals($foo, $res);
    }

    public function test_to_array_with_int()
    {
        $foo = 1;
        $res = $this->mock->_toArray($foo);
        $this->assertEquals(array($foo), $res);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_list_elements_ok()
    {
        $l = array(new Class1(), new Class1(), new Class1());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1"));
    }

    public function test_check_arg_list_elements_no_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected Class1, got Class2");
        $l = array(new Class1(), new Class1(), new Class2());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1"));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_list_elements_multi_class_ok()
    {
        $l = array(new Class1(), new Class2(), new Class1());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1", "Class2"));
    }

    public function test_check_arg_list_elements_multi_class_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected Class1, Class2, got Class3");
        $l = array(new Class1(), new Class2(), new Class3(), new Class2());
        $this->mock->_checkArgListElements("some_arg", $l, array("Class1", "Class2"));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_list_elements_string_or_int_ok()
    {
        $l = array(1, "foo");
        $this->mock->_checkArgListElements("some_arg", $l, array("string", "int"));
    }

    public function test_check_arg_list_elements_string_or_int_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected string, int, got Class1");
        $l = array(1, new Class1());
        $this->mock->_checkArgListElements("some_arg", $l, array("string", "int"));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_arg_list_ok()
    {
        $l = array("a" => 1, "b" => 2, "c" => 3);
        $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
            return is_string($k) && is_int($v);
        }, function ($k, $v) {
            return "expected keys of type string and integer values, got ($k => $v)";
        });
    }

    public function test_check_arg_list_not_ok_1()
    {
        $m = "expected keys of type string and integer values, got (4 => 3)";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': $m");
        $l = array("a" => 1, "b" => 2, 4 => 3);
        $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
            return is_string($k) && is_int($v);
        }, function ($k, $v) {
            return "expected keys of type string and integer values, got ($k => $v)";
        });
    }

    public function test_check_arg_list_not_ok_2()
    {
        $m = "expected keys of type string and integer values, got (c => d)";
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': $m");
        $l = array("a" => 1, "b" => 2, "c" => "d");
        $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
            return is_string($k) && is_int($v);
        }, function ($k, $v) {
            return "expected keys of type string and integer values, got ($k => $v)";
        });
    }

    /**
     * @doesNotPerformAssertions
     */
    public function test_check_float_arg_ok()
    {
        $this->mock->_checkFloatArg("some_arg", 1.73);
    }

    public function test_check_float_arg_not_ok()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Argument 'some_arg': expected float, got string 'foo'");
        $this->mock->_checkFloatArg("some_arg", "foo");
    }
}
