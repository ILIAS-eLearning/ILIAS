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

class VocabValueValidator implements DataValidatorInterface
{
    use DataFetcher;
    use VocabularyBridge;

    public function isValid(
        ElementInterface $element,
        bool $ignore_marker
    ): bool {
        $vocab_values = [];
        foreach ($this->vocabularies($element, $ignore_marker) as $vocabulary) {
            $vocab_values = array_merge(
                $vocab_values,
                iterator_to_array($vocabulary->values())
            );
        }
        return in_array(
            $this->normalize($this->dataValue($element, $ignore_marker)),
            array_map(
                fn (string $s) => $this->normalize($s),
                $vocab_values
            )
        );
    }

    /**
     * This is done to ensure backwards compatibility.
     */
    protected function normalize(string $string): string
    {
        return str_replace(' ', '', strtolower($string));
    }
}
