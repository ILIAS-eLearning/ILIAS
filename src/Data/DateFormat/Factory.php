<?php

declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\DateFormat;

/**
 * Factory for Date Formats
 */
class Factory
{
    protected FormatBuilder $builder;

    public function __construct(FormatBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Get the ISO 8601 date format (YYYY-MM-DD)
     */
    public function standard(): DateFormat
    {
        return $this->builder->year()->dash()->month()->dash()->day()->get();
    }

    /**
     * Get the builder to define a custom DateFormat
     */
    public function custom(): FormatBuilder
    {
        return $this->builder;
    }

    public function germanShort(): DateFormat
    {
        return $this->builder->day()->dot()->month()->dot()->year()->get();
    }

    public function germanLong(): DateFormat
    {
        return $this->builder->weekday()->comma()->space()
                             ->day()->dot()->month()->dot()->year()->get();
    }

    public function americanShort(): DateFormat
    {
        return $this->builder->month()->slash()->day()->slash()->year()->get();
    }

    public function fromUser(\ilObjUser $user): DateFormat
    {
        switch ($user->getDateFormat()) {
            case \ilCalendarSettings::DATE_FORMAT_DMY:
                return $this->germanShort();

            case \ilCalendarSettings::DATE_FORMAT_MDY:
                return $this->americanShort();

            case \ilCalendarSettings::DATE_FORMAT_YMD:
            default:
                return $this->standard();
        }
    }
}
