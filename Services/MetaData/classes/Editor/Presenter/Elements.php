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

class Elements implements ElementsInterface
{
    protected const SEPARATOR = ': ';
    protected const DELIMITER = ', ';

    protected UtilitiesInterface $utilities;
    protected DataInterface $data;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        UtilitiesInterface $utilities,
        DataInterface $data,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory
    ) {
        $this->utilities = $utilities;
        $this->data = $data;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
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
        $name = $element->getDefinition()->name();
        $exceptions = [
            'metadataSchema' => 'metadatascheme', 'lifeCycle' => 'lifecycle',
            'otherPlatformRequirements' => 'otherPlattformRequirements'
        ];
        $name = $exceptions[$name] ?? $name;

        $lang_key = 'meta_' . $this->camelCaseToSnakeCase($name);
        if ($plural) {
            $lang_key .= '_plural';
        }
        return $this->utilities->txt($lang_key);
    }

    public function nameWithParents(
        BaseElementInterface $element,
        ?BaseElementInterface $cut_off = null,
        bool $plural = false,
        bool $never_skip_initial = false
    ): string {
        $names = [];
        $el = $element;

        //skip the name of the element if it does not add any information
        $skip_arr = [Type::VOCAB_VALUE, Type::DURATION, Type::DATETIME, Type::STRING];
        $skip_initial =
            !$never_skip_initial &&
            !$this->dictionary->tagForElement($element)?->isLabelImportant() &&
            in_array($el->getDefinition()->dataType(), $skip_arr);

        while (!$el->isRoot()) {
            if ($el === $cut_off) {
                break;
            }
            if ($skip_initial) {
                $el = $el->getSuperElement();
                $skip_initial = false;
                continue;
            }
            array_unshift($names, $this->name($el));
            $el = $el->getSuperElement();
        }
        if (empty($names)) {
            return $this->name($element);
        }
        return implode(self::SEPARATOR, $names);
    }

    protected function camelCaseToSnakeCase(string $string): string
    {
        $string = preg_replace('/(?<=[a-z])(?=[A-Z])/', '_', $string);
        return strtolower($string);
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
