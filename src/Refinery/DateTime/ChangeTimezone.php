<?php
/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\DateTime;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Data\Result;
use ILIAS\Refinery\Transformation;

/**
 * change the timezone of php's \DateTimeImmutable
 */
class ChangeTimezone implements Transformation
{
    use DeriveApplyToFromTransform;

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
     * calculate the difference beween two timezones in seconds
     */
    protected function getTimezoneDelta(\DateTimeZone $tz1, \DateTimeZone $tz2) : int
    {
        $date1 = new \DateTimeImmutable('now', $tz1);
        $date2 = new \DateTimeImmutable('now', $tz2);
        $delta = $tz1->getOffset($date1) - $tz2->getOffset($date2);
        return $delta;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!$from instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException("$from is not a DateTimeImmutable-object", 1);
        }

        $offset = $this->getTimezoneDelta(
            $from->getTimezone(),
            $this->timezone
        );

        $to = clone $from;
        $to = $to
            ->setTimezone($this->timezone)
            ->modify("$offset seconds");
        return $to;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
