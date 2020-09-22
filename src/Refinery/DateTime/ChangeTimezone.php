<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\DateTime;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;

/**
 * Change the timezone (and only the timezone) of php's \DateTimeImmutable WITHOUT changing the date-value.
 * This will effectively be another point in time and space.
 */
class ChangeTimezone implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @var \DateTimeZone
     */
    private $timezone;

    /**
     * @param string $timezone
     * @param Factory $factory
     */
    public function __construct(string $timezone)
    {
        if (!in_array($timezone, timezone_identifiers_list())) {
            throw new \InvalidArgumentException("$timezone is not a valid timezone identifier", 1);
        }
        $this->timezone = new \DateTimeZone($timezone);
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!$from instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException("$from is not a DateTimeImmutable-object", 1);
        }
        
        $ts = $from->format('Y-m-d H:i:s');
        $to = new \DateTimeImmutable($ts, $this->timezone);
        return $to;
    }
}
