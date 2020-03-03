<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the parellel constraint
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ParallelTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->df = new Data\Factory();
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->f = new Validation\Factory($this->df, $this->lng);

        $this->less_than_3 = $this->f->custom(
            function ($value) {
                return $value < 3;
            },
            "not_less_than_3"
        );

        $this->less_than_5 = $this->f->custom(
            function ($value) {
                return $value < 5;
            },
            "not_less_than_5"
        );

        $this->c = $this->f->parallel([$this->less_than_3, $this->less_than_5]);
    }

    public function testAccepts()
    {
        $this->assertTrue($this->c->accepts(2));
    }

    public function testNotAccepts()
    {
        $this->assertFalse($this->c->accepts(4));
    }

    public function testCheckSucceed()
    {
        $this->c->check(2);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check(6);
    }

    public function testNoProblemWith()
    {
        $this->assertNull($this->c->problemWith(2));
    }

    public function testProblemWith1()
    {
        $this->lng
            ->expects($this->never())
            ->method("txt");

        $this->assertEquals("not_less_than_3", $this->c->problemWith(4));
    }

    public function testProblemWith2()
    {
        $this->lng
            ->expects($this->never())
            ->method("txt");

        $this->assertEquals("not_less_than_3 not_less_than_5", $this->c->problemWith(6));
    }

    public function testRestrictOk()
    {
        $ok = $this->df->ok(2);

        $res = $this->c->restrict($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk()
    {
        $not_ok = $this->df->ok(7);

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
        $this->assertEquals("This was a fault", $new_c->problemWith(7));
    }
}
