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
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\CheckerInterface as ConditionChecker;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;

class ElementHelper implements ElementHelperInterface
{
    protected HandlerInterface $slot_handler;
    protected PathFactory $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;
    protected ConditionChecker $condition_checker;

    public function __construct(
        HandlerInterface $slot_handler,
        PathFactory $path_factory,
        NavigatorFactoryInterface $navigator_factory,
        ConditionChecker $condition_checker
    ) {
        $this->slot_handler = $slot_handler;
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
        $this->condition_checker = $condition_checker;
    }

    public function slotForElement(ElementInterface $element): Identifier
    {
        foreach ($this->slotsForElementWithoutCondition($element) as $slot) {
            if ($this->condition_checker->doesElementFitSlot($element, $slot)) {
                return $slot;
            }
        }
        return Identifier::NULL;
    }

    /**
     * @return Identifier[]
     */
    public function slotsForElementWithoutCondition(ElementInterface $element): \Generator
    {
        yield from $this->slot_handler->allSlotsForPath($this->path_factory->toElement($element));
    }

    public function potentialSlotForElementByCondition(
        ElementInterface $element,
        ElementInterface $element_in_condition,
        string $value
    ): Identifier {
        return $this->slot_handler->identiferFromPathAndCondition(
            $this->path_factory->toElement($element),
            $this->path_factory->betweenElements($element, $element_in_condition),
            $value,
        );
    }

    public function findElementOfCondition(
        Identifier $slot,
        ElementInterface $element,
        ElementInterface ...$all_elements
    ): ?ElementInterface {
        if (!$this->slot_handler->isSlotConditional($slot)) {
            return null;
        }

        $condition_path = $this->slot_handler->conditionForSlot($slot)->path();
        $potential_result = $this->navigator_factory->navigator($condition_path, $element)
                                                    ->lastElementAtFinalStep();

        return in_array($potential_result, $all_elements, true) ? $potential_result : null;
    }
}
