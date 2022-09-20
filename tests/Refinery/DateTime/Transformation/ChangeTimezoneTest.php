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
use ILIAS\Refinery\DateTime;
use PHPUnit\Framework\TestCase;

class ChangeTimezoneTest extends TestCase
{
    private DateTime\Group $dt;

    protected function setUp(): void
    {
        $this->dt = new DateTime\Group();
    }

    public function testTransform(): void
    {
        $dat = '2019-05-26 13:15:01';
        $origin_tz = 'Europe/Berlin';
        $target_tz = 'Europe/London';
        $origin = new DateTimeImmutable($dat, new DateTimeZone($origin_tz));
        $expected = new DateTimeImmutable($dat, new DateTimeZone($target_tz));
        $trans = $this->dt->changeTimezone($target_tz);

        $this->assertEquals(
            $expected,
            $trans->transform($origin)
        );
    }

    public function testTransformValues(): void
    {
        $dat = '2019-05-26 13:15:01';
        $origin_tz = 'Europe/Berlin';
        $target_tz = 'America/El_Salvador';
        $origin = new DateTimeImmutable($dat, new DateTimeZone($origin_tz));
        $trans = $this->dt->changeTimezone($target_tz);
        $this->assertEquals(
            $dat,
            date_format($trans->transform($origin), 'Y-m-d H:i:s')
        );
    }

    public function testNullTransform(): void
    {
        $trans = $this->dt->changeTimezone('Europe/Berlin');
        $this->expectException(InvalidArgumentException::class);
        $trans->transform(null);
    }

    public function testInvalidTransform(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $trans = $this->dt->changeTimezone('Europe/Berlin');
        $trans->transform('erroneous');
    }

    public function testInvoke(): void
    {
        $dat = '2019/05/26 16:05:22';
        $origin_tz = 'Europe/Berlin';
        $target_tz = 'Europe/London';
        $origin = new DateTimeImmutable($dat, new DateTimeZone($origin_tz));
        $expected = new DateTimeImmutable($dat, new DateTimeZone($target_tz));
        $trans = $this->dt->changeTimezone($target_tz);
        $this->assertEquals($expected, $trans($origin));
    }

    public function testApplyToOK(): void
    {
        $trans = $this->dt->changeTimezone('Europe/London');
        $value = '2019/05/26';
        $origin = new DateTimeImmutable($value);
        $expected = new DateTimeImmutable($value, new DateTimeZone('Europe/London'));

        $df = new DataFactory();
        $ok = $df->ok($origin);

        $result = $trans->applyTo($ok);
        $this->assertEquals($expected, $result->value());
        $this->assertFalse($result->isError());
    }

    public function testApplyToFail(): void
    {
        $trans = $this->dt->changeTimezone('Europe/London');
        $df = new DataFactory();
        $ok = $df->ok('not_a_date');

        $result = $trans->applyTo($ok);
        $this->assertTrue($result->isError());
    }
}
