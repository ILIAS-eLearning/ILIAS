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
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;

class SequentialTest extends TestCase
{
    private DataFactory $df;
    private ilLanguage $lng;
    private Refinery $refinery;
    private Constraint $c;

    protected function setUp() : void
    {
        $this->df = new DataFactory();
        $this->lng = $this->createMock(ilLanguage::class);
        $this->refinery = new Refinery($this->df, $this->lng);

        $group = $this->refinery->custom();

        $greater_than_3 = $group->constraint(
            function ($value) {
                return $value > 3;
            },
            "not_greater_than_3"
        );

        $less_than_5 = $group->constraint(
            function ($value) {
                return $value < 5;
            },
            "not_less_than_5"
        );

        $this->c = $this->refinery
            ->logical()
            ->sequential([$greater_than_3, $less_than_5]);
    }

    public function testAccepts() : void
    {
        $this->assertTrue($this->c->accepts(4));
    }

    public function testNotAccepts() : void
    {
        $this->assertFalse($this->c->accepts(2));
    }

    public function testCheckSucceed() : void
    {
        $this->c->check(4);
        $this->assertTrue(true); // does not throw
    }

    public function testCheckFails() : void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->c->check(2);
    }

    public function testNoProblemWith() : void
    {
        $this->assertNull($this->c->problemWith(4));
    }

    public function testProblemWith1() : void
    {
        $this->lng
            ->expects($this->never())
            ->method("txt");

        $this->assertEquals("not_greater_than_3", $this->c->problemWith(2));
    }

    public function testProblemWith2() : void
    {
        $this->lng
            ->expects($this->never())
            ->method("txt");

        $this->assertEquals("not_less_than_5", $this->c->problemWith(6));
    }

    public function testRestrictOk() : void
    {
        $ok = $this->df->ok(4);

        $res = $this->c->applyTo($ok);
        $this->assertTrue($res->isOk());
    }

    public function testRestrictNotOk() : void
    {
        $not_ok = $this->df->ok(7);

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
        $this->assertEquals("This was a fault", $new_c->problemWith(7));
    }
}
