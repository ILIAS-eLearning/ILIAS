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

/**
 * Class ilObjFileImplementationAbstract
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilObjFileImplementationEmpty implements ilObjFileImplementationInterface
{
    public function getDirectory(int $a_version = 0): string
    {
        return '';
    }

    public function deleteVersions(?array $a_hist_entry_ids = null): void
    {
        // TODO: Implement deleteVersions() method.
    }

    public function getFileType(): string
    {
        return '';
    }

    public function handleChangedObjectTitle(string $new_title): void
    {
        return;
    }

    public function getStorageID(): ?string
    {
        return null;
    }

    public function getFileSize(): int
    {
        return 0;
    }

    public function getFile(?int $a_hist_entry_id = null): string
    {
        return '';
    }

    public function getVersion(): int
    {
        return 0;
    }

    public function getMaxVersion(): int
    {
        return 0;
    }

    public function sendFile(?int $a_hist_entry_id = null): void
    {
    }

    public function getFileExtension(): string
    {
        return '';
    }

    public function getVersions(?array $version_ids = null): array
    {
        return [];
    }
}
