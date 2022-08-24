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
}
