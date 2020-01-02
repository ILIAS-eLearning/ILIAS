<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

class MyValidationConstraintsCustom extends Validation\Constraints\Custom
{
    public function _getLngClosure()
    {
        return $this->getLngClosure();
    }
}

class MyToStringClass
{
    protected $str_repr;

    public function __toString()
    {
        return $this->str_repr;
    }
}

/**
 * TestCase for the custom constraints
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ValidationConstraintsCustomTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Validation\Factory
     */
    protected $f = null;

    public function setUp()
    {
        $is_ok = function ($value) {
            return false;
        };
        $this->txt_id = "TXT_ID";
        $error = function (callable $txt, $value) use ($txt_id) {
            return $txt($txt_id, $value);
        };
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->constraint = new MyValidationConstraintsCustom($is_ok, $error, new Data\Factory(), $this->lng);
    }

    public function testWithProblemBuilder()
    {
        $new_constraint = $this->constraint->withProblemBuilder(function () {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_constraint->problemWith(""));
    }

    public function testProblemBuilderRetrievesLngClosure()
    {
        $c = $this->constraint->withProblemBuilder(function ($txt) {
            $this->cls = $txt;
            return "";
        });
        $c->problemWith("");
        $this->assertTrue(is_callable($this->cls));
    }

    public function test_use_txt()
    {
        $txt_out = "'%s'";
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with($txt_id)
            ->willReturn($txt_out);

        $value = "VALUE";
        $problem = $this->constraint->problemWith($value);

        $this->assertEquals(sprintf($txt_out, $value), $problem);
    }

    public function test_exception_on_no_parameter()
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $this->expectException(\InvalidArgumentException::class);

        $lng_closure();
    }

    public function test_no_sprintf_on_one_parameter()
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $txt_out = "txt";
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with($this->txt_id)
            ->willReturn($txt_out);

        $res = $lng_closure($this->txt_id);

        $this->assertEquals($txt_out, $res);
    }

    public function test_gracefully_handle_arrays_and_objects()
    {
        $lng_closure = $this->constraint->_getLngClosure();

        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("id")
            ->willReturn("%s-%s-%s-%s-");

        $to_string = new MyToStringClass("foo");

        $res = $lng_closure("id", [], new \stdClass(), "foo", null);

        $this->assertEquals("array-" . \stdClass::class . "-foo-null-", $res);
    }
}
