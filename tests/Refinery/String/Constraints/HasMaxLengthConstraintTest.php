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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\String\HasMaxLength;
use ILIAS\Refinery\Constraint;
use PHPUnit\Framework\TestCase;

class HasMaxLengthConstraintTest extends TestCase
{
    private DataFactory $df;
    private ilLanguage $lng;
    private int $max_length;
    private Constraint $c;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        $this->lng = $this->createMock(ilLanguage::class);

        $this->max_length = 2;

        $this->c = new HasMaxLength(
            $this->max_length,
            $this->df,
            $this->lng
        );
    }

    public function testAccepts1(): void
    {
        $this->assertTrue($this->c->accepts("12"));
    }

    public function testAccepts2(): void
    {
        $this->assertTrue($this->c->accepts("1"));
    }

    public function testNotAccepts(): void
    {
        $this->assertFalse($this->c->accepts("123"));
    }

    public function testCheckSucceed(): void
    {
        $this->c->check("12");
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->c->check("123");
    }

    public function testNoProblemWith(): void
    {
        $this->assertNull($this->c->problemWith("12"));
    }

    public function testProblemWith(): void
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_max_length")
            ->willReturn("-%s-");

        $this->assertEquals("-2-", $this->c->problemWith("123"));
    }

    public function testRestrictOk(): void
    {
        $ok = $this->df->ok("12");

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk(): void
    {
        $not_ok = $this->df->ok("123");

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
        $this->assertEquals("This was a fault", $new_c->problemWith("123"));
    }
}
