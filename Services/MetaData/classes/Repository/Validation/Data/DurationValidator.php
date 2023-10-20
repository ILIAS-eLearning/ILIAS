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

class DurationValidator implements DataValidatorInterface
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
        if (!$this->data_helper->matchesDurationPattern($value)) {
            return false;
        }
        foreach ($this->data_helper->durationToIterator($value) as $match) {
            if (isset($match) && (int) $match < 0) {
                return false;
            }
        }
        return true;
    }
}
