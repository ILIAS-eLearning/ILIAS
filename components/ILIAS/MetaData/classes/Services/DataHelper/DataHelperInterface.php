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

namespace ILIAS\MetaData\Services\DataHelper;

use ILIAS\MetaData\Elements\Data\DataInterface;

interface DataHelperInterface
{
    public function makePresentable(DataInterface $data): string;

    /**
     * Translates strings in the LOM-internal duration format to arrays
     * consisting of in order years, months, days, hours, minutes, seconds.
     * Note that durations distinguish between a field being
     * set to zero or not set at all. In the latter case, the field has a null entry
     * in the returned array.
     * @return int[]|null[]
     */
    public function durationToArray(string $duration): array;

    /**
     * Translates strings in the LOM-internal duration format to seconds.
     * This is only a rough estimate, as LOM-durations do not have a start
     * date, so e.g.  each month is treated as 30 days.
     */
    public function durationToSeconds(string $duration): int;

    /**
     * Translates strings in the LOM-internal datetime format to
     * datetime objects.
     * Note that LOM datetimes in ILIAS only consist of a date, and not
     * a time.
     */
    public function datetimeToObject(string $datetime): \DateTimeImmutable;

    /**
     * Get a string in the LOM-internal duration format as specified by
     * the provided integers.
     */
    public function durationFromIntegers(
        ?int $years,
        ?int $months,
        ?int $days,
        ?int $hours,
        ?int $minutes,
        ?int $seconds
    ): string;

    /**
     * Translates datetime objects to strings in the LOM-internal datetime
     * format.
     * Note that LOM in ILIAS ignores the time part of any datetimes.
     */
    public function datetimeFromObject(\DateTimeImmutable $object): string;
}
