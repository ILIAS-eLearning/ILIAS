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

class NotConstraintTest extends TestCase
{
    private DataFactory $df;
    private Constraint $not_true;
    private Constraint $not_false;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        [$this->not_true, $this->not_false] = $this->notConstraints(
            $this->createStub(ILIAS\Language\Language::class)
        );
    }

    public function testAccepts(): void
    {
        $this->assertTrue($this->not_false->accepts(null));
    }

    public function testNotAccepts(): void
    {
        $this->assertFalse($this->not_true->accepts(null));
    }

    public function testCheckSucceed(): void
    {
        $this->not_false->check(null);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->not_true->check(null);
    }

    public function testNoProblemWith(): void
    {
        $this->assertNull($this->not_false->problemWith(null));
    }

    public function testProblemWith(): void
    {
        $lng = $this->createMock(ILIAS\Language\Language::class);
        $lng
            ->expects($this->once())
            ->method('txt')
            ->with('not_generic')
            ->willReturn('-%s-');

        [$not_true] = $this->notConstraints($lng);

        $this->assertEquals('-not_true-', $not_true->problemWith(null));
    }

    public function testRestrictOk(): void
    {
        $ok = $this->df->ok(null);

        $res = $this->not_false->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk(): void
    {
        $not_ok = $this->df->ok(null);

        $res = $this->not_true->applyTo($not_ok);
        $this->assertFalse($res->isOk());
    }

    public function testRestrictError(): void
    {
        $error = $this->df->error('error');

        $res = $this->not_false->applyTo($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder(): void
    {
        $new_c = $this->not_true->withProblemBuilder(static fn(): string => 'This was a fault');
        $this->assertEquals('This was a fault', $new_c->problemWith(null));
    }

    /**
     * @return array{Constraint, Constraint}
     */
    private function notConstraints(ILIAS\Language\Language $lng): array
    {
        $refinery = new Refinery($this->df, $lng);
        $group = $refinery->custom();

        return [
            $refinery->logical()->not($group->constraint(
                static fn($v): bool => true,
                'not_true'
            )),
            $refinery->logical()->not($group->constraint(
                static fn($v): bool => false,
                'not_false'
            )),
        ];
    }
}
