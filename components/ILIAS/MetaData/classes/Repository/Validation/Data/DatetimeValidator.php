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

namespace ILIAS\MetaData\Repository\Validation\Data;

use ILIAS\MetaData\Elements\ElementInterface;

class DatetimeValidator implements DataValidatorInterface
{
    use DataFetcher;

    /**
     * This monstrosity makes sure datetimes conform to the format given by LOM,
     * and picks out the relevant numbers.
     * match 1: YYYY, 2: MM, 3: DD, 4: hh, 5: mm, 6: ss, 7: s (arbitrary many
     * digits for decimal fractions of seconds), 8: timezone, either Z for
     * UTC or +- hh:mm (mm is optional)
     */
    public const DATETIME_REGEX = '/^(\d{4})(?:-(\d{2})(?:-(\d{2})' .
    '(?:T(\d{2})(?::(\d{2})(?::(\d{2})(?:\.(\d+)(Z|[+\-]' .
    '\d{2}(?::\d{2})?)?)?)?)?)?)?)?$/';

    public function isValid(
        ElementInterface $element,
        bool $ignore_marker
    ): bool {
        if (!preg_match(
            self::DATETIME_REGEX,
            $this->dataValue($element, $ignore_marker),
            $matches,
            PREG_UNMATCHED_AS_NULL
        )) {
            return false;
        }
        if (isset($matches[1]) && ((int) $matches[1]) < 1) {
            return false;
        }
        if (isset($matches[2]) &&
            (((int) $matches[2]) < 1 || ((int) $matches[2]) > 12)) {
            return false;
        }
        if (isset($matches[3]) &&
            (((int) $matches[3]) < 1 || ((int) $matches[3]) > 31)) {
            return false;
        }
        if (isset($matches[4]) && ((int) $matches[4]) > 23) {
            return false;
        }
        if (isset($matches[5]) && ((int) $matches[5]) > 59) {
            return false;
        }
        if (isset($matches[6]) && ((int) $matches[6]) > 59) {
            return false;
        }
        return true;
    }
}
