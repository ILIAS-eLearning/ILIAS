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
    public function get(): DateFormat
    {
        $df = new DateFormat($this->format);
        $this->format = [];
        return $df;
    }

    /**
     * Append tokens to format.
     */
    public function dot(): FormatBuilder
    {
        $this->format[] = DateFormat::DOT;
        return $this;
    }

    public function comma(): FormatBuilder
    {
        $this->format[] = DateFormat::COMMA;
        return $this;
    }

    public function dash(): FormatBuilder
    {
        $this->format[] = DateFormat::DASH;
        return $this;
    }

    public function slash(): FormatBuilder
    {
        $this->format[] = DateFormat::SLASH;
        return $this;
    }

    public function space(): FormatBuilder
    {
        $this->format[] = DateFormat::SPACE;
        return $this;
    }

    public function day(): FormatBuilder
    {
        $this->format[] = DateFormat::DAY;
        return $this;
    }

    public function dayOrdinal(): FormatBuilder
    {
        $this->format[] = DateFormat::DAY_ORDINAL;
        return $this;
    }

    public function weekday(): FormatBuilder
    {
        $this->format[] = DateFormat::WEEKDAY;
        return $this;
    }

    public function weekdayShort(): FormatBuilder
    {
        $this->format[] = DateFormat::WEEKDAY_SHORT;
        return $this;
    }

    public function week(): FormatBuilder
    {
        $this->format[] = DateFormat::WEEK;
        return $this;
    }

    public function month(): FormatBuilder
    {
        $this->format[] = DateFormat::MONTH;
        return $this;
    }

    public function monthSpelled(): FormatBuilder
    {
        $this->format[] = DateFormat::MONTH_SPELLED;
        return $this;
    }

    public function monthSpelledShort(): FormatBuilder
    {
        $this->format[] = DateFormat::MONTH_SPELLED_SHORT;
        return $this;
    }

    public function year(): FormatBuilder
    {
        $this->format[] = DateFormat::YEAR;
        return $this;
    }

    public function twoDigitYear(): FormatBuilder
    {
        $this->format[] = DateFormat::YEAR_TWO_DIG;
        return $this;
    }

    public function hours24(): FormatBuilder
    {
        $this->format[] = DateFormat::HOURS24;
        return $this;
    }

    public function hours12(): FormatBuilder
    {
        $this->format[] = DateFormat::HOURS12;
        return $this;
    }

    public function minutes(): FormatBuilder
    {
        $this->format[] = DateFormat::MINUTES;
        return $this;
    }

    public function seconds(): FormatBuilder
    {
        $this->format[] = DateFormat::SECONDS;
        return $this;
    }

    public function meridiem(): FormatBuilder
    {
        $this->format[] = DateFormat::MERIDIEM;
        return $this;
    }

    public function colon(): FormatBuilder
    {
        $this->format[] = DateFormat::COLON;
        return $this;
    }
}
