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
 * Interface ilObjFileImplementationInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilObjFileImplementationInterface
{
    /**
     * @deprecated
     */
    public function getDirectory(int $a_version = 0): string;

    /**
     * Deletes the specified history entries or all entries if no ids are specified.
     * @param array|null $a_hist_entry_ids The ids of the entries to delete or null to delete all entries
     * @deprecated
     */
    public function deleteVersions(?array $a_hist_entry_ids = null): void;

    public function getFileType(): string;

    public function getStorageID(): ?string;

    public function getFileSize(): int;

    /**
     * @deprecated
     */
    public function getFile(?int $a_hist_entry_id = null): string;

    public function getVersion(): int;

    public function getMaxVersion(): int;

    /**
     * @deprecated
     */
    public function sendFile(?int $a_hist_entry_id = null): void;

    public function handleChangedObjectTitle(string $new_title): void;

    /**
     * Returns the extension of the file name converted to lower-case.
     * e.g. returns 'pdf' for 'document.pdf'.
     */
    public function getFileExtension(): string;

    /**
     * Gets the file versions for this object.
     * @param array $version_ids The file versions to get. If not specified all versions are
     *                           returned.
     * @return array The file versions.
     *                           Example:  array (
     *                           'date' => '2019-07-25 11:19:51',
     *                           'user_id' => '6',
     *                           'obj_id' => '287',
     *                           'obj_type' => 'file',
     *                           'action' => 'create',
     *                           'info_params' => 'chicken_outlined.pdf,1,1',
     *                           'user_comment' => '',
     *                           'hist_entry_id' => '3',
     *                           'title' => NULL,
     *                           )
     */
    public function getVersions(?array $version_ids = null): array;
}
