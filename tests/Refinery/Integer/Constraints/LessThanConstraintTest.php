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

namespace ILIAS\Tests\Refinery\Integer\Constraints;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Integer\LessThan;
use ilLanguage;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class LessThanConstraintTest extends TestCase
{
    private Constraint $c;
    private ilLanguage $lng;
    private DataFactory $df;
    private int $less_than;

    protected function setUp(): void
    {
        $this->df = new DataFactory();
        $this->lng = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->less_than = 10;

        $this->c = new LessThan(
            $this->less_than,
            $this->df,
            $this->lng
        );
    }

    public function testAccepts(): void
    {
        $this->assertTrue($this->c->accepts(2));
    }

    public function testNotAccepts(): void
    {
        $this->assertFalse($this->c->accepts(10));
    }

    public function testCheckSucceed(): void
    {
        $this->c->check(2);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->c->check(11);
    }

    public function testNoProblemWith(): void
    {
        $this->assertNull($this->c->problemWith(1));
    }

    public function testProblemWith(): void
    {
        $this->lng
            ->expects($this->once())
            ->method("txt")
            ->with("not_less_than")
            ->willReturn("-%s-%s-");

        $this->assertEquals("-12-{$this->less_than}-", $this->c->problemWith("12"));
    }

    public function testRestrictOk(): void
    {
        $ok = $this->df->ok(1);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk(): void
    {
        $not_ok = $this->df->ok(1234);

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
        $this->assertEquals("This was a fault", $new_c->problemWith(13));
    }
}
