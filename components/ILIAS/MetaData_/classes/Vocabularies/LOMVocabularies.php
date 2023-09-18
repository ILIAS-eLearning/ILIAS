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

namespace ILIAS\MetaData\Vocabularies;

use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Vocabularies\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Vocabularies\Dictionary\DictionaryInitiatorInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;

class LOMVocabularies implements VocabulariesInterface
{
    protected DictionaryInitiatorInterface $initiator;
    protected NavigatorFactoryInterface $navigator_factory;

    protected DictionaryInterface $dictionary;

    public function __construct(
        DictionaryInitiatorInterface $dictionary_initiator,
        NavigatorFactoryInterface $navigator_factory
    ) {
        $this->initiator = $dictionary_initiator;
        $this->navigator_factory = $navigator_factory;
    }

    /**
     * @return VocabularyInterface[]
     */
    public function vocabulariesForElement(
        BaseElementInterface $element
    ): \Generator {
        if (!isset($this->dictionary)) {
            $this->dictionary = $this->initiator->get();
        }
        foreach ($this->dictionary->tagsForElement($element) as $tag) {
            yield $tag->vocabulary();
        }
    }

    /**
     * @return VocabularyInterface[]
     */
    public function filteredVocabulariesForElement(
        ElementInterface $element,
        bool $ignore_marker
    ): \Generator {
        $is_vocab_value = false;
        $source = null;
        if ($element->getDefinition()->dataType() === Type::VOCAB_VALUE) {
            $is_vocab_value = true;
            $source = $this->getDataValueFromRelativePath(
                $element,
                $this->initiator->pathFromValueToSource(),
                $ignore_marker
            );
        }
        foreach ($this->vocabulariesForElement($element) as $vocab) {
            if ($is_vocab_value && $vocab->source() !== $source) {
                continue;
            }
            if (!$this->checkCondition($element, $vocab, $ignore_marker)) {
                continue;
            }
            yield $vocab;
        }
    }

    protected function checkCondition(
        ElementInterface $element,
        VocabularyInterface $vocabulary,
        bool $ignore_marker
    ): bool {
        if (!$vocabulary->isConditional()) {
            return true;
        }
        $value = $this->getDataValueFromRelativePath(
            $element,
            $vocabulary->condition()->path(),
            $ignore_marker
        );
        if ($value === $vocabulary->condition()->value()) {
            return true;
        }
        return false;
    }

    protected function getDataValueFromRelativePath(
        ElementInterface $start,
        PathInterface $path,
        bool $ignore_marker
    ): ?string {
        $element = $this->navigator_factory->navigator(
            $path,
            $start
        )->lastElementAtFinalStep();

        if (!isset($element)) {
            return null;
        }
        if (
            !$ignore_marker &&
            ($element instanceof MarkableInterface) &&
            $element->isMarked()
        ) {
            return $element->getMarker()->dataValue();
        }
        return $element->getData()->value();
    }
}
