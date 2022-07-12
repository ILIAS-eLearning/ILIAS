<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\String\HasMinLength;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;

class HasMinLengthConstraintTest extends TestCase
{
    private DataFactory $df;
    private ilLanguage $lng;
    private int $min_length;
    private Constraint $c;

    protected function setUp() : void
    {
        $this->df = new DataFactory();
        $this->lng = $this->createMock(ilLanguage::class);

        $this->min_length = 10;

        $this->c = new HasMinLength(
            $this->min_length,
            $this->df,
            $this->lng
        );
    }

    public function testAccepts1() : void
    {
        $this->assertTrue($this->c->accepts("1234567890"));
    }

    public function testAccepts2() : void
    {
        $this->assertTrue($this->c->accepts("12345678901"));
    }

    public function testNotAccepts() : void
    {
        $this->assertFalse($this->c->accepts("123456789"));
    }

    public function testCheckSucceed() : void
    {
        $this->c->check("1234567890");
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails() : void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->c->check("");
    }

    public function testNoProblemWith() : void
    {
        $this->assertNull($this->c->problemWith("1234567890"));
    }

    public function testProblemWith() : void
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_min_length")
            ->willReturn("-%s-%s-");

        $this->assertEquals("-3-10-", $this->c->problemWith("123"));
    }

    public function testRestrictOk() : void
    {
        $ok = $this->df->ok("1234567890");

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk() : void
    {
        $not_ok = $this->df->ok("1234");

        $res = $this->c->applyTo($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictError() : void
    {
        $error = $this->df->error("error");

        $res = $this->c->applyTo($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder() : void
    {
        $new_c = $this->c->withProblemBuilder(static function () : string {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_c->problemWith(""));
    }
}
