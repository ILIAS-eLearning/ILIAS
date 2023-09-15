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
 * This class handles all operations on files for the forum object.
 * @author    Stefan Meyer <meyer@leifos.com>
 * @ingroup   ModulesForum
 */
interface ilFileDataForumInterface
{
    public function getObjId(): int;

    public function getPosId(): int;

    public function setPosId(int $posting_id): void;

    public function getForumPath(): string;
    /**
     * @return array<string, array{path: string, md5: string, name: string, size: int, ctime: string}>
     */
    public function getFilesOfPost(): array;

    public function moveFilesOfPost(int $new_frm_id = 0): bool;

    public function ilClone(int $new_obj_id, int $new_posting_id): bool;

    public function delete(array $posting_ids_to_delete = null): bool;

    public function storeUploadedFiles(): bool;

    public function unlinkFile(string $filename): bool;

    /**
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashed_filename): ?array;

    /**
     * @param string|string[] $hashed_filename_or_filenames
     */
    public function unlinkFilesByMD5Filenames($hashed_filename_or_filenames): bool;

    public function deliverFile(string $file): void;

    public function deliverZipFile(): bool;
}
