<?php

/* Copyright (c) 2017, 2018, Stefan Hecken <stefan.hecken@concepts-and-training.de>, Richard Klees <richard.klees@concepts-and-training.de, Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

class NotTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->df = new Data\Factory();
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->f = new Validation\Factory($this->df, $this->lng);

        $this->not_true = $this->f->not($this->f->custom(
            function ($v) {
                return true;
            },
            "not_true"
        ));

        $this->not_false = $this->f->not($this->f->custom(
            function ($v) {
                return false;
            },
            "not_false"
        ));
    }

    public function testAccepts()
    {
        $this->assertTrue($this->not_false->accepts(null));
    }

    public function testNotAccepts()
    {
        $this->assertFalse($this->not_true->accepts(null));
    }

    public function testCheckSucceed()
    {
        $this->not_false->check(null);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->not_true->check(null);
    }

    public function testNoProblemWith()
    {
        $this->assertNull($this->not_false->problemWith(null));
    }

    public function testProblemWith()
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_generic")
            ->willReturn("-%s-");

        $this->assertEquals("-not_true-", $this->not_true->problemWith(null));
    }

    public function testRestrictOk()
    {
        $ok = $this->df->ok(null);

        $res = $this->not_false->restrict($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk()
    {
        $not_ok = $this->df->ok(null);

        $res = $this->not_true->restrict($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictError()
    {
        $error = $this->df->error("error");

        $res = $this->not_false->restrict($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder()
    {
        $new_c = $this->not_true->withProblemBuilder(function () {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_c->problemWith(null));
    }
}
