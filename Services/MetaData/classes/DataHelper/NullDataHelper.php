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

class NullDataHelper implements DataHelperInterface
{
    public function matchesDurationPattern(string $string): bool
    {
        return false;
    }

    public function matchesDatetimePattern(string $string): bool
    {
        return false;
    }

    /**
     * @return int[]|null[]
     */
    public function durationToIterator(string $duration): \Generator
    {
        yield from [];
    }

    public function durationToSeconds(string $duration): int
    {
        return 0;
    }

    /**
     * @return int[]|null[]|string[]
     */
    public function datetimeToIterator(string $datetime): \Generator
    {
        yield from [];
    }

    public function datetimeToObject(string $datetime): \DateTimeImmutable
    {
        return new \DateTimeImmutable('@0');
    }

    public function durationFromIntegers(
        ?int $years,
        ?int $months,
        ?int $days,
        ?int $hours,
        ?int $minutes,
        ?int $seconds
    ): string {
        return '';
    }

    public function datetimeFromObject(\DateTimeImmutable $object): string
    {
        return '';
    }

    /**
     * @return string[]
     */
    public function getAllLanguages(): \Generator
    {
        yield from [];
    }
}
