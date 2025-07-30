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

namespace ILIAS\MetaData\Vocabularies\Slots;

use ILIAS\MetaData\Elements\ElementInterface;

interface ElementHelperInterface
{
    public function slotForElement(ElementInterface $element): Identifier;

    /**
     * Does not check the condition of the slots, so can return multiple slots
     * per element.
     * @return Identifier[]
     */
    public function slotsForElementWithoutCondition(ElementInterface $element): \Generator;

    public function potentialSlotForElementByCondition(
        ElementInterface $element,
        ElementInterface $element_in_condition,
        string $value
    ): Identifier;

    public function findElementOfCondition(
        Identifier $slot,
        ElementInterface $element,
        ElementInterface ...$all_elements
    ): ?ElementInterface;
}
