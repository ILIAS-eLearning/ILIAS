<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Data\Clock;

use ILIAS\Data\Clock\UtcClock;
use PHPUnit\Framework\TestCase;

class UtcClockTest extends TestCase
{
    private string $default_timezone;

    protected function setUp() : void
    {
        $this->default_timezone = date_default_timezone_get();
    }

    protected function tearDown() : void
    {
        date_default_timezone_set($this->default_timezone);
    }

    public function testUtcClockIsNotAffectedByGlobalTimezoneChanges() : void
    {
        date_default_timezone_set('Europe/Berlin');
        $clock = new UtcClock();

        self::assertSame('UTC', $clock->now()->getTimezone()->getName());
    }
}
