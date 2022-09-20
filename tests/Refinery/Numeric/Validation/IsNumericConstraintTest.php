<?php

declare(strict_types=1);

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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;

class IsNumericConstraintTest extends TestCase
{
    private DataFactory $df;
    private ilLanguage $lng;
    private Refinery $f;
    private \ILIAS\Refinery\Constraint $c;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        $this->lng = $this->createMock(ilLanguage::class);

        $this->f = new Refinery($this->df, $this->lng);

        $this->c = $this->f->numeric()->isNumeric();
    }

    public function testAccepts1(): void
    {
        $this->assertTrue($this->c->accepts(0));
    }

    public function testAccepts2(): void
    {
        $this->assertTrue($this->c->accepts("1"));
    }

    public function testAccepts3(): void
    {
        $this->assertTrue($this->c->accepts(1));
    }

    public function testAccepts4(): void
    {
        $this->assertTrue($this->c->accepts(0x102));
    }

    public function testAccepts5(): void
    {
        $this->assertTrue($this->c->accepts(0102));
    }

    public function testAccepts6(): void
    {
        $this->assertTrue($this->c->accepts(0b101));
    }

    public function testAccepts7(): void
    {
        $this->assertTrue($this->c->accepts(192e0));
    }

    public function testAccepts8(): void
    {
        $this->assertTrue($this->c->accepts(9.1));
    }

    public function testNotAccepts1(): void
    {
        $this->assertFalse($this->c->accepts(null));
    }

    public function testNotAccepts2(): void
    {
        $this->assertFalse($this->c->accepts("foo"));
    }

    public function testCheckSucceed(): void
    {
        $this->c->check(2);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails(): void
    {
        $this->lng
            ->method('txt')
            ->willReturnCallback(static function (string $value): string {
                return $value;
            })
        ;
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check("");
    }

    public function testNoProblemWith(): void
    {
        $this->assertNull($this->c->problemWith(2));
    }

    public function testProblemWith(): void
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_numeric")
            ->willReturn("-%s-");

        $this->assertEquals("-aa-", $this->c->problemWith("aa"));
    }

    public function testRestrictOk(): void
    {
        $ok = $this->df->ok(2);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk(): void
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

    public function testRestrictError(): void
    {
        $error = $this->df->error("error");

        $res = $this->c->applyTo($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder(): void
    {
        $new_c = $this->c->withProblemBuilder(static function (): string {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_c->problemWith(""));
    }
}
