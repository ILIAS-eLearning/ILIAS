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

    public function amend(DateFormat $format): FormatBuilder
    {
        return $this->builder->initWithFormat($format);
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

    public function withTime12(DateFormat $format): DateFormat
    {
        return $this->amend($format)
            ->space()->hours12()->colon()->minutes()->space()->meridiem()->get();
    }

    public function withTime24(DateFormat $format): DateFormat
    {
        return $this->amend($format)
            ->space()->hours24()->colon()->minutes()->get();
    }
}
