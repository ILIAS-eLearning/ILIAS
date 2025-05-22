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

namespace ILIAS\Data\Clock;

use DateTimeZone;
use ILIAS\Data\Clock\LocalClock;
use PHPUnit\Framework\TestCase;

class LocalClockTest extends TestCase
{
    private string $default_timezone;

    protected function setUp(): void
    {
        $this->default_timezone = date_default_timezone_get();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->default_timezone);
    }

    public function testUtcClockIsNotAffectedByGlobalTimezoneChanges(): void
    {
        date_default_timezone_set('UTC');

        $clock = new LocalClock(new DateTimeZone('Africa/Windhoek'));

        self::assertSame('Africa/Windhoek', $clock->now()->getTimezone()->getName());
    }
}
