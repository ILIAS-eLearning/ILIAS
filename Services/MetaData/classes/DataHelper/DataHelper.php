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

class DataHelper extends Constants implements DataHelperInterface
{
    public function matchesDurationPattern(string $string): bool
    {
        return (bool) preg_match(Constants::DURATION_REGEX, $string);
    }

    public function matchesDatetimePattern(string $string): bool
    {
        return (bool) preg_match(Constants::DATETIME_REGEX, $string);
    }

    /**
     * @return string[]|null[]
     */
    public function durationToIterator(string $duration): \Generator
    {
        if (!preg_match(
            Constants::DURATION_REGEX,
            $duration,
            $matches,
            PREG_UNMATCHED_AS_NULL
        )) {
            return;
        }
        yield from array_slice($matches, 1);
    }

    public function durationToSeconds(string $duration): int
    {
        $factors = [1, 12, 30, 24, 60, 60];
        $factor_index = 0;
        $result = 0;
        foreach ($this->durationToIterator($duration) as $number) {
            $result = $factors[$factor_index] * $result + $number;
            $factor_index++;
        }
        return $result;
    }

    /**
     * @return string[]|null[]
     */
    public function datetimeToIterator(string $datetime): \Generator
    {
        if (!preg_match(
            Constants::DATETIME_REGEX,
            $datetime,
            $matches,
            PREG_UNMATCHED_AS_NULL
        )) {
            return;
        }
        yield from array_slice($matches, 1);
    }

    public function datetimeToObject(string $datetime): \DateTimeImmutable
    {
        preg_match(
            Constants::DATETIME_REGEX,
            $datetime,
            $matches,
            PREG_UNMATCHED_AS_NULL
        );
        return new \DateTimeImmutable(
            ($matches[1] ?? '0000') . '-' .
            ($matches[2] ?? '01') . '-' .
            ($matches[3] ?? '01')
        );
    }

    public function durationFromIntegers(
        ?int $years,
        ?int $months,
        ?int $days,
        ?int $hours,
        ?int $minutes,
        ?int $seconds
    ): string {
        $has_time = !is_null($hours) || !is_null($minutes) || !is_null($seconds);

        if (is_null($years) && is_null($months) && is_null($days) && !$has_time) {
            return '';
        }

        $string = 'P';
        if (!is_null($years)) {
            $string .= max($years, 0) . 'Y';
        }
        if (!is_null($months)) {
            $string .= max($months, 0) . 'M';
        }
        if (!is_null($days)) {
            $string .= max($days, 0) . 'D';
        }

        if (!$has_time) {
            return $string;
        }
        $string .= 'T';
        if (!is_null($hours)) {
            $string .= max($hours, 0) . 'H';
        }
        if (!is_null($minutes)) {
            $string .= max($minutes, 0) . 'M';
        }
        if (!is_null($seconds)) {
            $string .= max($seconds, 0) . 'S';
        }

        return $string;
    }

    public function datetimeFromObject(\DateTimeImmutable $object): string
    {
        return $object->format('Y-m-d');
    }

    /**
     * @return string[]
     */
    public function getAllLanguages(): \Generator
    {
        yield from Constants::LANGUAGES;
    }
}
