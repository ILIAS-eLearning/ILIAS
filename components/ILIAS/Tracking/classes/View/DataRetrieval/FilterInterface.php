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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval;

interface FilterInterface
{
    public function withUserIds(int ...$ids): self;

    public function withObjectIds(int ...$ids): self;

    public function withOnlyDataOfObjectWithLPEnabled(
        bool $only_data_of_object_with_lp_enabled
    ): FilterInterface;

    /**
     * @return int[]
     */
    public function getUserIds(): array;

    /**
     * @return int[]
     */
    public function getObjectIds(): array;

    public function collectOnlyDataOfObjectsWithLPEnabled(): bool;

    public function hasObjectIds(): bool;

    public function hasUserIds(): bool;
}
