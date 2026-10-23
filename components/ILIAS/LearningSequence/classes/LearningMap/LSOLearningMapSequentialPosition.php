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

namespace ILIAS\LearningSequence\LearningMap;

/**
 * Tracks the position in a sequential learning map.
 */
class LSOLearningMapSequentialPosition extends LSOLearningMapPosition
{
    /** @var int The reference ID of the first item. */
    protected int $first_ref_id = 0;
    /** @var int The reference ID of the last item. */
    protected int $last_ref_id = 0;

    /**
     * Prepares the position for the given items.
     *
     * @param \LSLearnerItem[] $items
     */

    public function prepareForItems(array $items): void
    {
        $items = array_values($items);
        if ($items === []) {
            $this->first_ref_id = 0;
            $this->last_ref_id = 0;
            return;
        }

        $this->first_ref_id = $items[0]->getRefId();
        $this->last_ref_id = $items[count($items) - 1]->getRefId();
    }

    /**
     * Returns the reference ID of the first item.
     */
    protected function getStartRefId(): int
    {
        return $this->first_ref_id;
    }

    /**
     * Returns the reference ID of the last item.
     */
    protected function getEndRefId(): int
    {
        return $this->last_ref_id;
    }

}
