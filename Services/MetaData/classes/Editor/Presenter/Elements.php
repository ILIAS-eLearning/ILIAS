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

namespace ILIAS\MetaData\Editor\Presenter;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Presentation\ElementsInterface as ElementsPresentation;

class Elements implements ElementsInterface
{
    protected const SEPARATOR = ': ';
    protected const DELIMITER = ', ';

    protected DataInterface $data;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;
    protected ElementsPresentation $elements;

    public function __construct(
        DataInterface $data,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
        ElementsPresentation $elements
    ) {
        $this->data = $data;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
        $this->elements = $elements;
    }

    public function nameWithRepresentation(
        bool $plural,
        ElementInterface ...$elements
    ): string {
        if (empty($elements)) {
            return '';
        }
        $name = $this->name(
            $elements[0],
            $plural
        );
        $tag = $this->dictionary->tagForElement($elements[0]);
        if ($tag?->hasRepresentation()) {
            $values = $this->getDataValueStringByPath(
                $tag->representation(),
                ...$elements
            );
            if ($values !== '') {
                $name = implode(self::SEPARATOR, [$name, $values]);
            }
        }
        return $name;
    }

    public function preview(ElementInterface ...$elements): string
    {
        if (empty($elements)) {
            return '';
        }
        $tag = $this->dictionary->tagForElement($elements[0]);
        if (!$tag?->hasPreview()) {
            return '';
        }
        return $this->getDataValueStringByPath($tag->preview(), ...$elements);
    }

    public function name(
        BaseElementInterface $element,
        bool $plural = false
    ): string {
        return $this->elements->name($element, $plural);
    }

    public function nameWithParents(
        BaseElementInterface $element,
        ?BaseElementInterface $cut_off = null,
        bool $plural = false,
        bool $never_skip_initial = false
    ): string {
        //skip the name of the element if it does not add any information
        $skip_arr = [Type::VOCAB_VALUE, Type::DURATION, Type::DATETIME, Type::STRING];
        $skip_initial =
            !$never_skip_initial &&
            !$this->dictionary->tagForElement($element)?->isLabelImportant() &&
            in_array($element->getDefinition()->dataType(), $skip_arr);

        return $this->elements->nameWithParents(
            $element,
            $cut_off,
            $plural,
            $skip_initial
        );
    }

    protected function getDataValueStringByPath(
        PathInterface $path,
        ElementInterface ...$elements
    ): string {
        $values = [];
        foreach ($elements as $element) {
            $navigator = $this->navigator_factory->navigator(
                $path,
                $element
            );
            foreach ($navigator->elementsAtFinalStep() as $el) {
                if (
                    ($data = $el->getData())->type() !== Type::NULL &&
                    $data->value() !== ''
                ) {
                    $values[] = $this->data->dataValue($data);
                }
            }
        }
        return implode(self::DELIMITER, $values);
    }
}
