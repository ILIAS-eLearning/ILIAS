<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

class IsArrayOfConstraintTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->df = new Data\Factory();
        $this->lng = $this->createMock(\ilLanguage::class);
        $this->f = new Validation\Factory($this->df, $this->lng);

        $this->on_element = $this->createMock(Validation\Constraint::class);

        $this->c = $this->f->isArrayOf($this->on_element);
    }

    public function testAccepts()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, true));

        $this->assertTrue($this->c->accepts([1, 2]));
    }

    public function testNotAccepts()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, false));

        $this->assertFalse($this->c->accepts([1, 2, 3]));
    }

    public function testNotAcceptsNoneArrays()
    {
        $this->assertFalse($this->c->accepts(1));
    }

    public function testCheckSucceed()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, true));

        $this->c->check([1, 2]);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, false));

        $this->expectException(\UnexpectedValueException::class);
        $this->c->check([1, 2]);
    }


    public function testCheckFailsOnNoneArray()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check(1);
    }

    public function testNoProblemWith()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, true));

        $this->assertNull($this->c->problemWith([1, 2]));
    }

    public function testProblemWith()
    {
        $this->on_element
            ->expects($this->exactly(3))
            ->method("problemWith")
            ->withConsecutive([1], [2], [3])
            ->will($this->onConsecutiveCalls(null, "2", "3"));

        $this->lng
            ->expects($this->exactly(1))
            ->method("txt")
            ->withConsecutive(["not_an_array_of"])
            ->willReturn("-%s-");

        $this->assertEquals("-2 3-", $this->c->problemWith([1,2,3]));
    }

    public function testProblemWithNoneArray()
    {
        $this->lng
            ->expects($this->exactly(1))
            ->method("txt")
            ->withConsecutive(["not_an_array"])
            ->willReturn("-%s-");

        $this->assertEquals("-integer-", $this->c->problemWith(1));
    }

    public function testRestrictOk()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, true));

        $ok = $this->df->ok([1, 2]);

        $res = $this->c->restrict($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk()
    {
        $this->on_element
            ->expects($this->exactly(2))
            ->method("accepts")
            ->withConsecutive([1], [2])
            ->will($this->onConsecutiveCalls(true, false));

        $not_ok = $this->df->ok([1,2]);

        $res = $this->c->restrict($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictNotOkForNoneArray()
    {
        $not_ok = $this->df->ok(1);

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
        $this->assertEquals("This was a fault", $new_c->problemWith(2));
    }
}
