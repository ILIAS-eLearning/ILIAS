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

class VocabSourceValidator implements DataValidatorInterface
{
    use DataFetcher;
    use VocabularyBridge;

    public function isValid(
        ElementInterface $element,
        bool $ignore_marker
    ): bool {
        $sources = [];
        foreach ($this->vocabularies($element, $ignore_marker) as $vocabulary) {
            $sources[] = $vocabulary->source();
        }
        return in_array($this->dataValue($element, $ignore_marker), $sources);
    }
}
