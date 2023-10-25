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

namespace ILIAS\MetaData\Paths\Steps;

use ILIAS\MetaData\Paths\Filters\FilterInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Structure\StructureElement;

class NavigatorBridge
{
    /**
     * @return BaseElementInterface[]
     */
    public function getNextElementsByStep(
        StepInterface $step,
        BaseElementInterface ...$elements
    ): \Generator {
        $next_elements = [];
        foreach ($elements as $element) {
            $next_elements = array_merge(
                $next_elements,
                iterator_to_array($this->getNextElementsByName($element, $step->name()))
            );
        }

        foreach ($step->filters() as $filter) {
            switch ($filter->type()) {
                case FilterType::NULL:
                    break;

                case FilterType::MDID:
                    $next_elements = $this->filterByMDID(
                        $filter,
                        ...$next_elements
                    );
                    break;

                case FilterType::INDEX:
                    $next_elements = $this->filterByIndex(
                        $filter,
                        ...$next_elements
                    );
                    break;

                case FilterType::DATA:
                    $next_elements = $this->filterByData(
                        $filter,
                        ...$next_elements
                    );
                    break;
            }
        }

        yield from $next_elements;
    }

    public function getParents(
        BaseElementInterface ...$elements
    ): \Generator {
        $next_elements = [];
        foreach ($elements as $element) {
            $parent = $element->getSuperElement();
            if (!in_array($parent, $next_elements, true)) {
                $next_elements[] = $parent;
            }
        }
        yield from $next_elements;
    }

    /**
     * @return BaseElementInterface[]
     */
    protected function getNextElementsByName(
        BaseElementInterface $element,
        string|StepToken $name
    ): \Generator {
        if ($name === StepToken::SUPER) {
            if ($super = $element->getSuperElement()) {
                yield $super;
            }
            return;
        }

        foreach ($element->getSubElements() as $sub) {
            if (strtolower($sub->getDefinition()->name()) === strtolower($name)) {
                yield $sub;
            }
        }
    }

    /**
     * @return BaseElementInterface[]
     */
    protected function filterByMDID(
        FilterInterface $filter,
        BaseElementInterface ...$elements
    ): \Generator {
        foreach ($elements as $element) {
            $id = $element->getMDID();
            $id = is_int($id) ? (string) $id : $id->value;
            if ($element instanceof StructureElement) {
                yield $element;
            }
            if (in_array($id, iterator_to_array($filter->values()), true)) {
                yield $element;
            }
        }
    }

    /**
     * @return BaseElementInterface[]
     */
    protected function filterByIndex(
        FilterInterface $filter,
        BaseElementInterface ...$elements
    ): \Generator {
        $index = 0;
        $filter_values = [];
        $select_last = false;
        foreach ($filter->values() as $value) {
            if (preg_match('/^\d+$/', $value)) {
                $filter_values[] = (int) $value;
            } else {
                $select_last = true;
            }
        }
        foreach ($elements as $element) {
            if (
                in_array($index, $filter_values, true) ||
                ($select_last && array_key_last($elements) === $index)
            ) {
                yield $element;
            }
            $index++;
        }
    }

    /**
     * @return BaseElementInterface[]
     */
    protected function filterByData(
        FilterInterface $filter,
        BaseElementInterface ...$elements
    ): \Generator {
        foreach ($elements as $element) {
            if (!($element instanceof ElementInterface)) {
                continue;
            }
            $data = $element->getData()->value();
            if ($element instanceof MarkableInterface && $element->isMarked()) {
                $data = $element->getMarker()->dataValue();
            }
            if (in_array($data, iterator_to_array($filter->values()), true)) {
                yield $element;
            }
        }
    }
}
