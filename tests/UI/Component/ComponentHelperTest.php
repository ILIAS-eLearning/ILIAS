<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

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
class ComponentHelperTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
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

    public function test_check_arg_ok()
    {
        try {
            $this->mock->_checkArg("some_arg", true, "some message");
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_arg_not_ok()
    {
        try {
            $this->mock->_checkArg("some_arg", false, "some message");
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': some message", $e->getMessage());
        }
    }

    public function test_check_int_arg_ok()
    {
        try {
            $this->mock->_checkIntArg("some_arg", 1);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_int_arg_not_ok()
    {
        try {
            $this->mock->_checkIntArg("some_arg", "foo");
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected integer, got string 'foo'", $e->getMessage());
        }
    }

    public function test_check_string_arg_ok()
    {
        try {
            $this->mock->_checkStringArg("some_arg", "bar");
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_string_arg_not_ok()
    {
        try {
            $this->mock->_checkStringArg("some_arg", 1);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected string, got integer '1'", $e->getMessage());
        }
    }

    public function test_check_bool_arg_ok()
    {
        try {
            $this->mock->_checkBoolArg("some_arg", true);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_bool_arg_not_ok()
    {
        try {
            $this->mock->_checkBoolArg("some_arg", 1);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected bool, got integer '1'", $e->getMessage());
        }
    }

    public function test_check_arg_instanceof_ok()
    {
        try {
            $this->mock->_checkArgInstanceOf("some_arg", $this->mock, ComponentMock::class);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_arg_instanceof_not_ok()
    {
        try {
            $this->mock->_checkArgInstanceOf("some_arg", $this, ComponentMock::class);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected ComponentMock, got ComponentHelperTest", $e->getMessage());
        }
    }



    public function test_check_arg_is_element_ok()
    {
        try {
            $this->mock->_checkArgIsElement("some_arg", "bar", array("foo", "bar"), "foobar");
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_string_arg_is_element_not_ok()
    {
        try {
            $this->mock->_checkArgIsElement("some_arg", "baz", array("foo", "bar"), "foobar");
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected foobar, got 'baz'", $e->getMessage());
        }
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

    public function test_check_arg_list_elements_ok()
    {
        $l = array(new Class1(), new Class1(), new Class1());
        try {
            $this->mock->_checkArgListElements("some_arg", $l, array("Class1"));
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_arg_list_elements_no_ok()
    {
        $l = array(new Class1(), new Class1(), new Class2());
        try {
            $this->mock->_checkArgListElements("some_arg", $l, array("Class1"));
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected Class1, got Class2", $e->getMessage());
        }
    }

    public function test_check_arg_list_elements_multi_class_ok()
    {
        $l = array(new Class1(), new Class2(), new Class1());
        try {
            $this->mock->_checkArgListElements("some_arg", $l, array("Class1", "Class2"));
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_arg_list_elements_multi_class_not_ok()
    {
        $l = array(new Class1(), new Class2(), new Class3(), new Class2());
        try {
            $this->mock->_checkArgListElements("some_arg", $l, array("Class1", "Class2"));
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected Class1, Class2, got Class3", $e->getMessage());
        }
    }

    public function test_check_arg_list_elements_string_or_int_ok()
    {
        $l = array(1, "foo");
        try {
            $this->mock->_checkArgListElements("some_arg", $l, array("string", "int"));
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_arg_list_elements_string_or_int_not_ok()
    {
        $l = array(1, new Class1());
        try {
            $this->mock->_checkArgListElements("some_arg", $l, array("string", "int"));
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected string, int, got Class1", $e->getMessage());
        }
    }

    public function test_check_arg_list_ok()
    {
        $l = array("a" => 1, "b" => 2, "c" => 3);
        try {
            $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
                return is_string($k) && is_int($v);
            }, function ($k, $v) {
                return "expected keys of type string and integer values, got ($k => $v)";
            });
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_arg_list_not_ok_1()
    {
        $l = array("a" => 1, "b" => 2, 4 => 3);
        try {
            $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
                return is_string($k) && is_int($v);
            }, function ($k, $v) {
                return "expected keys of type string and integer values, got ($k => $v)";
            });
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $m = "expected keys of type string and integer values, got (4 => 3)";
            $this->assertEquals("Argument 'some_arg': $m", $e->getMessage());
        }
    }

    public function test_check_arg_list_not_ok_2()
    {
        $l = array("a" => 1, "b" => 2, "c" => "d");
        try {
            $this->mock->_checkArgList("some_arg", $l, function ($k, $v) {
                return is_string($k) && is_int($v);
            }, function ($k, $v) {
                return "expected keys of type string and integer values, got ($k => $v)";
            });

            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $m = "expected keys of type string and integer values, got (c => d)";
            $this->assertEquals("Argument 'some_arg': $m", $e->getMessage());
        }
    }

    public function test_check_float_arg_ok()
    {
        try {
            $this->mock->_checkFloatArg("some_arg", 1.73);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function test_check_float_arg_not_ok()
    {
        try {
            $this->mock->_checkFloatArg("some_arg", "foo");
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals("Argument 'some_arg': expected float, got string 'foo'", $e->getMessage());
        }
    }
}
