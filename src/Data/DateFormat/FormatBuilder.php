<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * Builds a Date Format with split up elements to ease conversion.
 * Internal constants are based on options for php date format.
 */
class FormatBuilder
{
    /** @var string[] */
    private array $format = [];

    /**
     * Get the configured DateFormat and reset format.
     */
    public function get() : DateFormat
    {
        $df = new DateFormat($this->format);
        $this->format = [];
        return $df;
    }

    /**
     * Append tokens to format.
     */
    public function dot() : FormatBuilder
    {
        $this->format[] = DateFormat::DOT;
        return $this;
    }

    public function comma() : FormatBuilder
    {
        $this->format[] = DateFormat::COMMA;
        return $this;
    }

    public function dash() : FormatBuilder
    {
        $this->format[] = DateFormat::DASH;
        return $this;
    }

    public function slash() : FormatBuilder
    {
        $this->format[] = DateFormat::SLASH;
        return $this;
    }

    public function space() : FormatBuilder
    {
        $this->format[] = DateFormat::SPACE;
        return $this;
    }

    public function day() : FormatBuilder
    {
        $this->format[] = DateFormat::DAY;
        return $this;
    }

    public function dayOrdinal() : FormatBuilder
    {
        $this->format[] = DateFormat::DAY_ORDINAL;
        return $this;
    }

    public function weekday() : FormatBuilder
    {
        $this->format[] = DateFormat::WEEKDAY;
        return $this;
    }

    public function weekdayShort() : FormatBuilder
    {
        $this->format[] = DateFormat::WEEKDAY_SHORT;
        return $this;
    }

    public function week() : FormatBuilder
    {
        $this->format[] = DateFormat::WEEK;
        return $this;
    }

    public function month() : FormatBuilder
    {
        $this->format[] = DateFormat::MONTH;
        return $this;
    }

    public function monthSpelled() : FormatBuilder
    {
        $this->format[] = DateFormat::MONTH_SPELLED;
        return $this;
    }

    public function monthSpelledShort() : FormatBuilder
    {
        $this->format[] = DateFormat::MONTH_SPELLED_SHORT;
        return $this;
    }

    public function year() : FormatBuilder
    {
        $this->format[] = DateFormat::YEAR;
        return $this;
    }

    public function twoDigitYear() : FormatBuilder
    {
        $this->format[] = DateFormat::YEAR_TWO_DIG;
        return $this;
    }
}
