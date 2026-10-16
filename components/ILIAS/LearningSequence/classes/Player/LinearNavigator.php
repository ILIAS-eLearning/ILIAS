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

namespace ILIAS\LearningSequence\Player;

/**
 * Provides index-based navigation through the items of a learning sequence.
 */
class LinearNavigator implements LSNavigator
{
    /**
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[] The item directly following the current item.
     */
    public function getSuccessors(array $items, \LSLearnerItem $current): array
    {
        $position = $this->findPosition($items, $current);
        $next = $position + 1;
        if ($next >= 0 && $next < count($items)) {
            return [$items[$next]];
        }
        return [];
    }

    /**
     * @param \LSItem[] $items
     * @return \LSItem[] The item structurally following the current item.
     */
    public function getStructuralSuccessors(array $items, \LSItem $current): array
    {
        $position = $this->findPosition($items, $current);
        $next = $position + 1;
        if ($next >= 0 && $next < count($items)) {
            return [$items[$next]];
        }
        return [];
    }

    /**
     * @param \LSLearnerItem[] $items
     * @return \LSLearnerItem[] The item directly preceding the current item.
     */
    public function getPredecessors(array $items, \LSLearnerItem $current): array
    {
        $position = $this->findPosition($items, $current);
        $previous = $position - 1;
        if ($previous >= 0 && $previous < count($items)) {
            return [$items[$previous]];
        }
        return [];
    }

    /**
     * Allows leaving every item in linear navigation.
     */
    public function canLeave(\LSLearnerItem $current): bool
    {
        return true;
    }

    /**
     * Allows entering every item in linear navigation.
     */
    public function canEnter(\LSLearnerItem $target): bool
    {
        return true;
    }

    /**
     * Allows entering every item because linear navigation has no edge conditions.
     */
    public function canEnterIgnoringEdges(\LSLearnerItem $target): bool
    {
        return true;
    }

    /**
     * Allows entering every target from every current item.
     */
    public function canEnterFrom(\LSLearnerItem $current, \LSLearnerItem $target): bool
    {
        return true;
    }

    /**
     * Does not preload data because linear navigation has no state to load.
     *
     * @param \LSItem[] $items
     */
    public function preload(array $items): void
    {
    }

    /**
     * Returns no input-condition identifiers because linear navigation has no conditions.
     *
     * @return int[]
     */
    public function getInputConditionIds(\LSLearnerItem $item): array
    {
        return [];
    }

    /**
     * Returns no output-condition identifiers because linear navigation has no conditions.
     *
     * @return int[]
     */
    public function getOutputConditionIds(\LSLearnerItem $item): array
    {
        return [];
    }

    /**
     * Finds the array index of an item by its reference identifier.
     *
     * @param \LSItem[] $items
     * @throws \Exception If the item is not part of the given items.
     */
    protected function findPosition(array $items, \LSItem $item): int
    {
        foreach ($items as $index => $candidate) {
            if ($candidate->getRefId() === $item->getRefId()) {
                return $index;
            }
        }
        throw new \Exception("This is not a valid item.", 1);
    }
}
