<?php

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

declare(strict_types=1);

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class IsNumericConstraintTest extends TestCase
{
    private DataFactory $df;
    private ILIAS\Language\Language&Stub $lng;
    private \ILIAS\Refinery\Constraint $c;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        $this->lng = $this->createStub(ILIAS\Language\Language::class);
        $this->c = $this->numericConstraint($this->lng);
    }

    public function testAccepts1(): void
    {
        $this->assertTrue($this->c->accepts(0));
    }

    public function testAccepts2(): void
    {
        $this->assertTrue($this->c->accepts('1'));
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
        $this->assertFalse($this->c->accepts('foo'));
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
            ->willReturnCallback(static fn(string $value): string => $value);
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check('');
    }

    public function testNoProblemWith(): void
    {
        $this->assertNull($this->c->problemWith(2));
    }

    public function testProblemWith(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->once())
            ->method('txt')
            ->with('not_numeric')
            ->willReturn('-%s-');

        $this->assertEquals('-aa-', $this->numericConstraint($lng)->problemWith('aa'));
    }

    public function testRestrictOk(): void
    {
        $ok = $this->df->ok(2);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->once())
            ->method('txt')
            ->with('not_numeric_empty_string')
            ->willReturn('-%s-');

        $not_ok = $this->df->ok('');

        $res = $this->numericConstraint($lng)->applyTo($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictError(): void
    {
        $error = $this->df->error('error');

        $res = $this->c->applyTo($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder(): void
    {
        $new_c = $this->c->withProblemBuilder(static fn(): string => 'This was a fault');
        $this->assertEquals('This was a fault', $new_c->problemWith(''));
    }

    private function numericConstraint(ILIAS\Language\Language $lng): \ILIAS\Refinery\Constraint
    {
        return (new Refinery($this->df, $lng))->numeric()->isNumeric();
    }
}
