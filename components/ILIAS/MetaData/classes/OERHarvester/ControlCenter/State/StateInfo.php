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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\State;

class StateInfo implements StateInfoInterface
{
    /**
     * @param Status[]  $all_statuses
     * @param Action[]  $relevant_actions
     * @param Action[]  $unavailable_actions
     * @param int[]  $eligible_copyright_entry_ids
     */
    public function __construct(
        protected bool $is_publishing_relevant,
        protected Status $current_status,
        protected array $all_statuses,
        protected array $relevant_actions,
        protected array $unavailable_actions,
        protected array $eligible_copyright_entry_ids,
        protected bool $has_eligible_copyright
    ) {
    }

    public function isPublishingRelevant(): bool
    {
        return $this->is_publishing_relevant;
    }

    /**
     * @return Status[]
     */
    public function getAllPossibleStatuses(): array
    {
        return $this->all_statuses;
    }

    public function getCurrentStatus(): Status
    {
        return $this->current_status;
    }

    /**
     * @return Action[]
     */
    public function getRelevantActions(): array
    {
        return $this->relevant_actions;
    }

    public function isActionAvailable(Action $action): bool
    {
        return in_array($action, $this->relevant_actions) && !in_array($action, $this->unavailable_actions);
    }

    /**
     * @return int[]
     */
    public function getAllEligibleCopyrightEntryIDs(): array
    {
        return $this->eligible_copyright_entry_ids;
    }

    public function hasEligibleCopyright(): bool
    {
        return $this->has_eligible_copyright;
    }
}
