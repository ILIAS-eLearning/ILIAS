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

namespace ILIAS\MetaData\Vocabularies\Slots\Conditions;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Vocabularies\Slots\HandlerInterface as SlotsHandler;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface as NavigatorFactory;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;

class Checker implements CheckerInterface
{
    protected SlotsHandler $slots_handler;
    protected PathFactory $path_factory;
    protected NavigatorFactory $navigator_factory;

    public function __construct(
        SlotsHandler $slots_handler,
        PathFactory $path_factory,
        NavigatorFactory $navigator_factory
    ) {
        $this->slots_handler = $slots_handler;
        $this->path_factory = $path_factory;
        $this->navigator_factory = $navigator_factory;
    }

    public function doesElementFitSlot(
        ElementInterface $element,
        SlotIdentifier $slot,
        bool $ignore_markers = true
    ): bool {
        $slot_path = $this->slots_handler->pathForSlot($slot);
        $path_to_element = $this->path_factory->toElement($element);

        if ($slot_path->toString() !== $path_to_element->toString()) {
            return false;
        }

        if (!$this->slots_handler->isSlotConditional($slot)) {
            return true;
        }

        $condition = $this->slots_handler->conditionForSlot($slot);
        return $this->checkCondition($element, $condition, $ignore_markers);
    }

    protected function checkCondition(
        ElementInterface $element,
        ConditionInterface $condition,
        bool $ignore_markers
    ): bool {
        $value = $this->getDataValueFromRelativePath(
            $element,
            $condition->path(),
            $ignore_markers
        );
        return $value === $condition->value();
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
