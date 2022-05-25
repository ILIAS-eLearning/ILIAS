<?php declare(strict_types=1);

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

namespace ILIAS\Data\DateFormat;

/**
 * A Date Format provides a format definition akin to PHP's date formatting options,
 * but stores the single elements/options as array to ease conversion into other formats.
 */
class DateFormat
{
    public const DOT = '.';
    public const COMMA = ',';
    public const DASH = '-';
    public const SLASH = '/';
    public const SPACE = ' ';
    public const DAY = 'd';
    public const DAY_ORDINAL = 'jS';
    public const WEEKDAY = 'l';
    public const WEEKDAY_SHORT = 'D';
    public const WEEK = 'W';
    public const MONTH = 'm';
    public const MONTH_SPELLED = 'F';
    public const MONTH_SPELLED_SHORT = 'M';
    public const YEAR = 'Y';
    public const YEAR_TWO_DIG = 'y';

    public const TOKENS = [
        self::DOT,
        self::COMMA,
        self::DASH,
        self::SLASH,
        self::SPACE,
        self::DAY,
        self::DAY_ORDINAL,
        self::WEEKDAY,
        self::WEEKDAY_SHORT,
        self::WEEK,
        self::MONTH,
        self::MONTH_SPELLED,
        self::MONTH_SPELLED_SHORT,
        self::YEAR,
        self::YEAR_TWO_DIG
    ];

    /** @var string[] */
    protected array $format = [];

    public function __construct(array $format)
    {
        $this->validateFormatElelements($format);
        $this->format = $format;
    }

    public function validateFormatElelements(array $format) : void
    {
        foreach ($format as $entry) {
            if (!in_array($entry, self::TOKENS, true)) {
                throw new \InvalidArgumentException("not a valid token for date-format", 1);
            }
        }
    }

    /**
     * Get the elements of the format as array.
     * @return string[]
     */
    public function toArray() : array
    {
        return $this->format;
    }

    /**
     * Get the format as string.
     */
    public function toString() : string
    {
        return implode('', $this->format);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function applyTo(\DateTimeImmutable $datetime) : string
    {
        return $datetime->format($this->toString());
    }
}
