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

namespace ILIAS\MetaData\Editor\Vocabulary;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Dispatch\ReaderInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Vocabularies\Slots\ElementHelperInterface;

class Adapter implements AdapterInterface
{
    protected ReaderInterface $reader;
    protected ElementHelperInterface $element_helper;

    protected array $cached_vocabularies_by_slot = [];

    public function __construct(
        ReaderInterface $reader,
        ElementHelperInterface $element_helper
    ) {
        $this->reader = $reader;
        $this->element_helper = $element_helper;
    }

    public function findElementOfCondition(
        SlotIdentifier $slot,
        ElementInterface $element,
        ElementInterface ...$all_elements
    ): ?ElementInterface {
        return $this->element_helper->findElementOfCondition($slot, $element, ...$all_elements);
    }

    public function slotForElement(ElementInterface $element): SlotIdentifier
    {
        return $this->element_helper->slotForElement($element);
    }

    /**
     * @return SlotIdentifier[]
     */
    public function slotsForElementWithoutCondition(ElementInterface $element): \Generator
    {
        return $this->element_helper->slotsForElementWithoutCondition($element);
    }

    public function potentialSlotForElementByCondition(
        ElementInterface $element,
        ElementInterface $element_in_condition,
        string $value
    ): SlotIdentifier {
        return $this->element_helper->potentialSlotForElementByCondition(
            $element,
            $element_in_condition,
            $value
        );
    }

    /**
     * @return VocabularyInterface[]
     */
    protected function vocabulariesForSlot(
        SlotIdentifier $slot
    ): \Generator {
        if (!isset($this->cached_vocabularies_by_slot[$slot->value])) {
            $this->cached_vocabularies_by_slot[$slot->value] = iterator_to_array(
                $this->reader->activeVocabulariesForSlots($slot),
                false
            );
        }
        yield from $this->cached_vocabularies_by_slot[$slot->value];
    }

    public function doesSlotHaveVocabularies(SlotIdentifier $slot): bool
    {
        return $this->vocabulariesForSlot($slot)->current() !== null;
    }

    public function doesSlotAllowCustomInput(SlotIdentifier $slot): bool
    {
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            if (!$vocab->allowsCustomInputs()) {
                return false;
            }
        }
        return true;
    }

    public function isValueInVocabulariesForSlot(
        SlotIdentifier $slot,
        string $value
    ): bool {
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            if (in_array($value, iterator_to_array($vocab->values()), true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string[]
     */
    public function valuesInVocabulariesForSlot(
        SlotIdentifier $slot,
        ?string $add_if_not_included = null
    ): \Generator {
        $values = [];
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            $values_from_vocab = iterator_to_array($vocab->values());
            $values = array_merge($values, $values_from_vocab);
        }

        if (isset($add_if_not_included) && !in_array($add_if_not_included, $values)) {
            array_unshift($values, $add_if_not_included);
        }
        yield from $values;
    }

    public function sourceMapForSlot(SlotIdentifier $slot): \Closure
    {
        $sources_by_value = [];
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            $values_from_vocab = iterator_to_array($vocab->values());
            $sources_by_value = array_merge(
                $sources_by_value,
                array_fill_keys($values_from_vocab, $vocab->source())
            );
        }

        return function (string $value) use ($sources_by_value) {
            return $sources_by_value[$value] ?? null;
        };
    }
}
