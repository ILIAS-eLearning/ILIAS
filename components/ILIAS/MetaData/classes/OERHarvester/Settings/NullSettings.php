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
    public function getObjectTypesSelectedForPublishing(): array
    {
        return [];
    }

    public function isObjectTypeSelectedForPublishing(string $type): bool
    {
        return false;
    }

    /**
     * @return int[]
     */
    public function getCopyrightEntryIDsSelectedForPublishing(): array
    {
        return [];
    }

    public function isCopyrightEntryIDSelectedForPublishing(int $id): bool
    {
        return false;
    }

    public function saveCopyrightEntryIDsSelectedForPublishing(int ...$ids): void
    {
    }

    public function isEditorialStepEnabled(): bool
    {
        return false;
    }

    public function saveEditorialStepEnabled(bool $enabled): void
    {
    }

    public function getContainerRefIDForEditorialStep(): int
    {
        return 0;
    }

    public function saveContainerRefIDForEditorialStep(int $ref_id): void
    {
    }

    public function getContainerRefIDForPublishing(): int
    {
        return 0;
    }

    public function saveContainerRefIDForPublishing(int $ref_id): void
    {
    }

    /**
     * @return string[]
     */
    public function getObjectTypesEligibleForPublishing(): array
    {
        return [];
    }

    public function saveObjectTypesSelectedForPublishing(string ...$types): void
    {
    }

    public function isManualPublishingEnabled(): bool
    {
        return false;
    }

    public function saveManualPublishingEnabled(bool $enabled): void
    {
    }

    public function isAutomaticPublishingEnabled(): bool
    {
        return false;
    }

    public function saveAutomaticPublishingEnabled(bool $enabled): void
    {
    }
}
