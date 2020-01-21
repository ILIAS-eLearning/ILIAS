<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Refinery\Factory;
use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class IsNumericConstraintTest extends TestCase
{
    public function setUp() : void
    {
        $this->df = new Data\Factory();
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->f = new Factory($this->df, $this->lng);

        $this->c = $this->f->numeric()->isNumeric();
    }

    public function testAccepts1()
    {
        $this->assertTrue($this->c->accepts(0));
    }

    public function testAccepts2()
    {
        $this->assertTrue($this->c->accepts("1"));
    }

    public function testAccepts3()
    {
        $this->assertTrue($this->c->accepts(1));
    }

    public function testAccepts4()
    {
        $this->assertTrue($this->c->accepts(0x102));
    }

    public function testAccepts5()
    {
        $this->assertTrue($this->c->accepts(0102));
    }

    public function testAccepts6()
    {
        $this->assertTrue($this->c->accepts(0b101));
    }

    public function testAccepts7()
    {
        $this->assertTrue($this->c->accepts(192e0));
    }

    public function testAccepts8()
    {
        $this->assertTrue($this->c->accepts(9.1));
    }

    public function testNotAccepts1()
    {
        $this->assertFalse($this->c->accepts(null));
    }

    public function testNotAccepts2()
    {
        $this->assertFalse($this->c->accepts("foo"));
    }

    public function testCheckSucceed()
    {
        $this->c->check(2);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check("");
    }

    public function testNoProblemWith()
    {
        $this->assertNull($this->c->problemWith(2));
    }

    public function testProblemWith()
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_numeric")
            ->willReturn("-%s-");

        $this->assertEquals("-aa-", $this->c->problemWith("aa"));
    }

    public function testRestrictOk()
    {
        $ok = $this->df->ok(2);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk()
    {
        $not_ok = $this->df->ok("");

        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_numeric_empty_string")
            ->willReturn("-%s-");

        $res = $this->c->applyTo($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictError()
    {
        $error = $this->df->error("error");

        $res = $this->c->applyTo($error);
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
