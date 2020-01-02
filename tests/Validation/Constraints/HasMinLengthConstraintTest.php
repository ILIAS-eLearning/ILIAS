<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

class HasMinLengthConstraintTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->df = new Data\Factory();
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->f = new Validation\Factory($this->df, $this->lng);

        $this->min_length = 10;

        $this->c = $this->f->hasMinLength($this->min_length);
    }

    public function testAccepts1()
    {
        $this->assertTrue($this->c->accepts("1234567890"));
    }

    public function testAccepts2()
    {
        $this->assertTrue($this->c->accepts("12345678901"));
    }

    public function testNotAccepts()
    {
        $this->assertFalse($this->c->accepts("123456789"));
    }

    public function testCheckSucceed()
    {
        $this->c->check("1234567890");
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check("");
    }

    public function testNoProblemWith()
    {
        $this->assertNull($this->c->problemWith("1234567890"));
    }

    public function testProblemWith()
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_min_length")
            ->willReturn("-%s-%s-");

        $this->assertEquals("-3-10-", $this->c->problemWith("123"));
    }

    public function testRestrictOk()
    {
        $ok = $this->df->ok("1234567890");

        $res = $this->c->restrict($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk()
    {
        $not_ok = $this->df->ok("1234");

        $res = $this->c->restrict($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictError()
    {
        $error = $this->df->error("error");

        $res = $this->c->restrict($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder()
    {
        $new_c = $this->c->withProblemBuilder(function () {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_c->problemWith(""));
    }
}
