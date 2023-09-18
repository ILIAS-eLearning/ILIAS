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

namespace ILIAS\MetaData\Editor\Full\Services;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Data\Type;

class DataFinder
{
    /**
     * @return ElementInterface[]
     */
    public function getDataCarryingElements(
        ElementInterface $start_element,
        bool $skip_vocab_source = false
    ): \Generator {
        $elements = $this->getDataElementsInSubElements(
            $start_element,
            $skip_vocab_source,
            0
        );
        yield from $elements;
    }

    /**
     * @return ElementInterface[]
     */
    protected function getDataElementsInSubElements(
        ElementInterface $current_element,
        bool $skip_vocab_source,
        int $depth
    ): array {
        if ($depth > 20) {
            throw new \ilMDEditorException('LOM Structure is nested to deep.');
        }
        $elements = [];
        $type = $current_element->getDefinition()->dataType();
        if (
            $type !== Type::NULL &&
            !($skip_vocab_source && $type === Type::VOCAB_SOURCE)
        ) {
            $elements[] = $current_element;
        }
        foreach ($current_element->getSubElements() as $sub) {
            $elements = array_merge(
                $elements,
                $this->getDataElementsInSubElements(
                    $sub,
                    $skip_vocab_source,
                    $depth + 1
                )
            );
        }
        return $elements;
    }
}
