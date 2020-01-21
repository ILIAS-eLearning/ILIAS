<?php

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\Data\DateFormat\DateFormat;

/**
 * This describes the datetime-field.
 */
interface DateTime extends Input
{
    /**
     * Get an input like this using the given format.
     */
    public function withFormat(DateFormat $format) : DateTime;

    /**
     * Get the date-format of this input.
     */
    public function getFormat() : DateFormat;

    /**
     * Get an input like this using the given timezone.
     */
    public function withTimezone(string $tz) : DateTime;

    /**
     * Get the timezone of this input.
     * @return null|string
     */
    public function getTimezone();

    /**
     * Limit accepted values to datetime past (and including) the given $datetime.
     */
    public function withMinValue(\DateTimeImmutable $datetime) : DateTime;

    /**
     * Return the lowest value the input accepts.
     * @return  \DateTime | null
     */
    public function getMinValue();

    /**
     * Limit accepted values to datetime before (and including) the given value.
     */
    public function withMaxValue(\DateTimeImmutable $datetime) : DateTime;

    /**
     * Return the maximum date the input accepts.
     * @return  \DateTime | null
     */
    public function getMaxValue();

    /**
     * Input both date and time.
     * @return  DateTime
     */
    public function withUseTime(bool $with_time) : DateTime;

    /**
     * Should the input be used to get both date and time?
     * @return  DateTime
     */
    public function getUseTime() : bool;

    /**
     * Use this Input for a time-value rather than a date.
     * @return  DateTime
     */
    public function withTimeOnly(bool $time_only) : DateTime;

    /**
     * Should the input be used to get a time only?
     * @return  DateTime
     */
    public function getTimeOnly() : bool;
}
