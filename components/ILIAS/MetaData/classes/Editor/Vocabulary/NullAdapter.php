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
use ILIAS\MetaData\Vocabularies\Slots\Identifier;

class NullAdapter implements AdapterInterface
{
    public function findElementOfCondition(
        SlotIdentifier $slot,
        ElementInterface $element,
        ElementInterface ...$all_elements
    ): ?ElementInterface {
        return null;
    }

    public function slotForElement(ElementInterface $element): SlotIdentifier
    {
        return SlotIdentifier::NULL;
    }

    /**
     * @return SlotIdentifier[]
     */
    public function slotsForElementWithoutCondition(ElementInterface $element): \Generator
    {
        yield from [];
    }

    public function potentialSlotForElementByCondition(
        ElementInterface $element,
        ElementInterface $element_in_condition,
        string $value
    ): SlotIdentifier {
        return SlotIdentifier::NULL;
    }

    public function doesSlotHaveVocabularies(
        SlotIdentifier $slot
    ): bool {
        return false;
    }

    public function doesSlotAllowCustomInput(
        SlotIdentifier $slot,
    ): bool {
        return false;
    }

    public function isValueInVocabulariesForSlot(
        SlotIdentifier $slot,
        string $value
    ): bool {
        return false;
    }

    /**
     * @return string[]
     */
    public function valuesInVocabulariesForSlot(
        SlotIdentifier $slot,
        ?string $add_if_not_included = null
    ): \Generator {
        yield from [];
    }

    public function sourceMapForSlot(SlotIdentifier $slot): \Closure
    {
        return function () {
        };
    }
}
