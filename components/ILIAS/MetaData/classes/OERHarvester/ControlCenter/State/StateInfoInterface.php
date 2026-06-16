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

interface StateInfoInterface
{
    public function isPublishingRelevant(): bool;

    /**
     * @return Status[]
     */
    public function getAllPossibleStatuses(): array;

    public function getCurrentStatus(): Status;

    /**
     * @return Action[]
     */
    public function getRelevantActions(): array;

    /**
     * Returns false both if the action should not be offered at all
     * in the current context, and if the action should be shown as
     * unavailable.
     * Takes into account access checks.
     */
    public function isActionAvailable(Action $action): bool;

    /**
     * @return int[]
     */
    public function getAllEligibleCopyrightEntryIDs(): array;

    public function hasEligibleCopyright(): bool;
}
