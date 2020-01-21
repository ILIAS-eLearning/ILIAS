<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Refinery\Integer\Constraints;

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class GreaterThanConstraintTest extends TestCase
{
    /**
     * @var Data\Factory
     */
    private $df;

    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @var integer
     */
    private $greater_than;

    public function setUp() : void
    {
        $this->df = new Data\Factory();
        $this->lng = $this->getMockBuilder(\ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->greater_than = 10;

        $this->c = new \ILIAS\Refinery\Integer\GreaterThan(
            $this->greater_than,
            $this->df,
            $this->lng
        );
    }

    public function testAccepts()
    {
        $this->assertTrue($this->c->accepts(12));
    }

    public function testNotAccepts()
    {
        $this->assertFalse($this->c->accepts(2));
    }

    public function testCheckSucceed()
    {
        $this->c->check(12);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check(2);
    }

    public function testNoProblemWith()
    {
        $this->assertNull($this->c->problemWith(12));
    }

    public function testProblemWith()
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_greater_than")
            ->willReturn("-%s-%s-");

        $this->assertEquals("-2-10-", $this->c->problemWith(2));
    }

    public function testRestrictOk()
    {
        $ok = $this->df->ok(12);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk()
    {
        $not_ok = $this->df->ok(2);

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
        $this->assertEquals("This was a fault", $new_c->problemWith(2));
    }
}
