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

namespace ILIAS\MetaData\OERHarvester\Settings;

class NullSettings implements SettingsInterface
{
    /**
     * @return string[]
     */
    public function getObjectTypesSelectedForHarvesting(): array
    {
        return [];
    }

    public function isObjectTypeSelectedForHarvesting(string $type): bool
    {
        return false;
    }

    /**
     * @return int[]
     */
    public function getCopyrightEntryIDsSelectedForHarvesting(): array
    {
        return [];
    }

    public function isCopyrightEntryIDSelectedForHarvesting(int $id): bool
    {
        return false;
    }

    public function saveCopyrightEntryIDsSelectedForHarvesting(int ...$ids): void
    {
    }

    public function getContainerRefIDForHarvesting(): int
    {
        return 0;
    }

    public function saveContainerRefIDForHarvesting(int $ref_id): void
    {
    }

    public function getContainerRefIDForExposing(): int
    {
        return 0;
    }

    public function saveContainerRefIDForExposing(int $ref_id): void
    {
    }

    /**
     * @return string[]
     */
    public function getObjectTypesEligibleForHarvesting(): array
    {
        return [];
    }

    public function saveObjectTypesSelectedForHarvesting(string ...$types): void
    {
    }
}
