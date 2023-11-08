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

use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Elements\ElementInterface;

class PropertiesFetcher
{
    protected DictionaryInterface $dictionary;
    protected PresenterInterface $presenter;
    protected DataFinder $data_finder;

    public function __construct(
        DictionaryInterface $dictionary,
        PresenterInterface $presenter,
        DataFinder $data_finder
    ) {
        $this->dictionary = $dictionary;
        $this->presenter = $presenter;
        $this->data_finder = $data_finder;
    }

    /**
     * @return string[]
     */
    public function getPropertiesByPreview(
        ElementInterface $element
    ): \Generator {
        $sub_els = [];
        foreach ($element->getSubElements() as $sub) {
            if ($sub->isScaffold()) {
                continue;
            }
            $tag = $this->dictionary->tagForElement($sub);
            $label = $this->presenter->elements()->nameWithRepresentation(
                $tag?->isCollected() && $tag?->isLastInTree(),
                $sub
            );
            $sub_els[$label][] = $sub;
        }
        foreach ($sub_els as $label => $els) {
            $value = $this->presenter->elements()->preview(
                ...$els
            );
            yield $label => $value;
        }
    }

    /**
     * @return string[]
     */
    public function getPropertiesByData(
        ElementInterface $element
    ): \Generator {
        $data_els = $this->data_finder->getDataCarryingElements(
            $element,
            true
        );
        foreach ($data_els as $data_el) {
            if ($data_el->isScaffold()) {
                continue;
            }
            $title = $this->presenter->elements()->nameWithParents(
                $data_el,
                $element
            );
            $value = $this->presenter->data()->dataValue($data_el->getData());
            yield $title => $value;
        }
    }
}
