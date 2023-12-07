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

namespace ILIAS\Refinery\DateTime;

use ILIAS\Refinery\Transformable;
use DateTimeZone;
use InvalidArgumentException;
use DateTimeImmutable;

/**
 * Change the timezone (and only the timezone) of php's \DateTimeImmutable WITHOUT changing the date-value.
 * This will effectively be another point in time and space.
 */
class ChangeTimezone implements Transformable
{
    private DateTimeZone $timezone;

    public function __construct(string $timezone)
    {
        if (!in_array($timezone, timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException("$timezone is not a valid timezone identifier", 1);
        }
        $this->timezone = new DateTimeZone($timezone);
    }

    public function transform($from)
    {
        if (!$value instanceof DateTimeImmutable) {
            throw new UnexpectedValueException("$value is not a DateTimeImmutable-object");
        }
        $ts = $from->format('Y-m-d H:i:s');

        return new DateTimeImmutable($ts, $this->timezone);
    }
}
