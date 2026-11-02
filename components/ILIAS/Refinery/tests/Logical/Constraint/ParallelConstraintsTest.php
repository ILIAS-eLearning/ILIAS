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

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;

class ParallelConstraintsTest extends TestCase
{
    private DataFactory $df;
    private Constraint $c;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        $this->c = $this->parallelConstraint($this->createStub(ILIAS\Language\Language::class));
    }

    public function testAccepts(): void
    {
        $this->assertTrue($this->c->accepts(2));
    }

    public function testNotAccepts(): void
    {
        $this->assertFalse($this->c->accepts(4));
    }

    public function testCheckSucceed(): void
    {
        $this->c->check(2);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->c->check(6);
    }

    public function testNoProblemWith(): void
    {
        $this->assertNull($this->c->problemWith(2));
    }

    public function testProblemWith1(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->never())
            ->method('txt');

        $this->assertEquals('not_less_than_3', $this->parallelConstraint($lng)->problemWith(4));
    }

    public function testProblemWith2(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->never())
            ->method('txt');

        $this->assertEquals('not_less_than_3 not_less_than_5', $this->parallelConstraint($lng)->problemWith(6));
    }

    public function testRestrictOk(): void
    {
        $ok = $this->df->ok(2);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk(): void
    {
        $not_ok = $this->df->ok(7);

        $res = $this->c->applyTo($not_ok);
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
        $this->assertEquals('This was a fault', $new_c->problemWith(7));
    }

    private function parallelConstraint(ILIAS\Language\Language $lng): Constraint
    {
        $refinery = new Refinery($this->df, $lng);
        $group = $refinery->custom();

        $less_than_3 = $group->constraint(
            static fn($value): bool => $value < 3,
            'not_less_than_3'
        );

        $less_than_5 = $group->constraint(
            static fn($value): bool => $value < 5,
            'not_less_than_5'
        );

        return $refinery->logical()->parallel([$less_than_3, $less_than_5]);
    }
}
