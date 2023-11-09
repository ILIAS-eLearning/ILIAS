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
use ILIAS\MetaData\DataHelper\DataHelperInterface;

class DatetimeValidator implements DataValidatorInterface
{
    use DataFetcher;

    protected DataHelperInterface $data_helper;

    public function __construct(
        DataHelperInterface $data_helper
    ) {
        $this->data_helper = $data_helper;
    }

    public function isValid(
        ElementInterface $element,
        bool $ignore_marker
    ): bool {
        $value = $this->dataValue($element, $ignore_marker);
        if (!$this->data_helper->matchesDatetimePattern($value)) {
            return false;
        }

        $matches = iterator_to_array($this->data_helper->datetimeToIterator($value));

        if (isset($matches[0]) && ((int) $matches[0]) < 1) {
            return false;
        }
        if (isset($matches[1]) &&
            (((int) $matches[1]) < 1 || ((int) $matches[1]) > 12)) {
            return false;
        }
        if (isset($matches[2]) &&
            (((int) $matches[2]) < 1 || ((int) $matches[2]) > 31)) {
            return false;
        }
        if (isset($matches[3]) && ((int) $matches[3]) > 23) {
            return false;
        }
        if (isset($matches[4]) && ((int) $matches[4]) > 59) {
            return false;
        }
        if (isset($matches[5]) && ((int) $matches[5]) > 59) {
            return false;
        }
        return true;
    }
}
