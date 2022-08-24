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

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;

class NotTest extends TestCase
{
    private DataFactory $df;
    private ilLanguage $lng;
    private Refinery $refinery;
    private Constraint $not_true;
    private Constraint $not_false;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        $this->lng = $this->createMock(ilLanguage::class);
        $this->refinery = new Refinery($this->df, $this->lng);

        $group = $this->refinery->custom();

        $this->not_true = $this->refinery->logical()->not($group->constraint(
            static function ($v): bool {
                return true;
            },
            "not_true"
        ));

        $this->not_false = $this->refinery->logical()->not($group->constraint(
            static function ($v): bool {
                return false;
            },
            "not_false"
        ));
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
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_generic")
            ->willReturn("-%s-");

        $this->assertEquals("-not_true-", $this->not_true->problemWith(null));
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
        $error = $this->df->error("error");

        $res = $this->not_false->applyTo($error);
        $this->assertSame($error, $res);
    }

    public function testWithProblemBuilder(): void
    {
        $new_c = $this->not_true->withProblemBuilder(static function (): string {
            return "This was a fault";
        });
        $this->assertEquals("This was a fault", $new_c->problemWith(null));
    }
}
