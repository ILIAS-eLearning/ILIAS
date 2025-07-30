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

use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Elements\ElementInterface;

interface AdapterInterface
{
    public function findElementOfCondition(
        SlotIdentifier $slot,
        ElementInterface $element,
        ElementInterface ...$all_elements
    ): ?ElementInterface;

    public function slotForElement(ElementInterface $element): SlotIdentifier;

    /**
     * @return SlotIdentifier[]
     */
    public function slotsForElementWithoutCondition(ElementInterface $element): \Generator;

    public function potentialSlotForElementByCondition(
        ElementInterface $element,
        ElementInterface $element_in_condition,
        string $value
    ): SlotIdentifier;

    public function doesSlotHaveVocabularies(
        SlotIdentifier $slot
    ): bool;

    public function doesSlotAllowCustomInput(
        SlotIdentifier $slot,
    ): bool;

    public function isValueInVocabulariesForSlot(
        SlotIdentifier $slot,
        string $value
    ): bool;

    /**
     * @return string[]
     */
    public function valuesInVocabulariesForSlot(
        SlotIdentifier $slot,
        ?string $add_if_not_included = null
    ): \Generator;

    /**
     * Returned closure takes a string value as argument,
     * and returns the source of the vocabulary it's from,
     * or null if it can't be found in a vocabulary
     * of the slot.
     */
    public function sourceMapForSlot(SlotIdentifier $slot): \Closure;
}
