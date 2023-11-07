<?php

declare(strict_types=1);

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

use DateTimeImmutable;
use DateTimeZone;

class LocalClock implements ClockInterface
{
    private DateTimeZone $time_zone;

    public function __construct(DateTimeZone $time_zone)
    {
        $this->time_zone = $time_zone;
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->time_zone);
    }
}
