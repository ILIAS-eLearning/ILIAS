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

namespace ILIAS\MetaData\DataHelper;

interface DataHelperInterface
{
    public function matchesDurationPattern(string $string): bool;

    public function matchesDatetimePattern(string $string): bool;

    /**
     * Returns in sequence years, months, days, hours, minutes, seconds.
     * Note that durations distinguish between a field being
     * set to zero or not set at all. In the latter case, the field is yielded
     * as null.
     * @return int[]|null[]
     */
    public function durationToIterator(string $duration): \Generator;

    public function durationToSeconds(string $duration): int;

    /**
     * Returns in sequence:
     * YYYY, MM, DD, hh, mm, ss, s (arbitrary many
     * digits for decimal fractions of seconds), 8: timezone, either Z for
     * UTC or +- hh:mm (mm is optional)
     * Note that datetimes distinguish between a field being
     * set to zero or not set at all. In the latter case, the field is yielded
     * as null.
     * @return int[]|null[]|string[]
     */
    public function datetimeToIterator(string $datetime): \Generator;

    public function datetimeToObject(string $datetime): \DateTimeImmutable;

    public function durationFromIntegers(
        ?int $years,
        ?int $months,
        ?int $days,
        ?int $hours,
        ?int $minutes,
        ?int $seconds
    ): string;

    /**
     * Note that LOM in ILIAS ignores the time part of any datetimes.
     */
    public function datetimeFromObject(\DateTimeImmutable $object): string;

    /**
     * @return string[]
     */
    public function getAllLanguages(): \Generator;
}
