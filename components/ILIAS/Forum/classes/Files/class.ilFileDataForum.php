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

class ilFileDataForum implements ilFileDataForumInterface
{
    /** @var array<int, ilForumPost> */
    private array $posting_cache = [];
    private readonly ilFileDataForumRCImplementation $rc_implementation;

    public function __construct(
        private readonly int $obj_id = 0,
        private readonly int $pos_id = 0
    ) {
        $this->rc_implementation = new ilFileDataForumRCImplementation(
            $this->obj_id,
            $this->pos_id
        );
    }

    private function getCurrentPosting(): ilForumPost
    {
        return $this->posting_cache[$this->pos_id] ?? ($this->posting_cache[$this->pos_id] = new ilForumPost(
            $this->pos_id
        ));
    }

    public function getObjId(): int
    {
        return $this->rc_implementation->getObjId();
    }

    public function getPosId(): int
    {
        return $this->rc_implementation->getPosId();
    }

    public function setPosId(int $posting_id): void
    {
        $this->rc_implementation->setPosId($posting_id);
    }

    public function getForumPath(): string
    {
        return $this->rc_implementation->getForumPath();
    }

    /**
     * @return array<string, array{path: string, md5: string, name: string, size: int, ctime: string}>
     */
    public function getFilesOfPost(): array
    {
        return $this->rc_implementation->getFilesOfPost();
    }

    public function moveFilesOfPost(int $new_frm_id = 0): bool
    {
        return $this->rc_implementation->moveFilesOfPost($new_frm_id);
    }

    public function ilClone(int $new_obj_id, int $new_posting_id): bool
    {
        return $this->rc_implementation->ilClone($new_obj_id, $new_posting_id);
    }

    public function delete(array $posting_ids_to_delete = null): bool
    {
        return $this->rc_implementation->delete($posting_ids_to_delete);
    }

    public function storeUploadedFiles(): bool
    {
        return $this->rc_implementation->storeUploadedFiles();
    }

    public function unlinkFile(string $filename): bool
    {
        return $this->rc_implementation->unlinkFile($filename);
    }

    /**
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashed_filename): ?array
    {
        return $this->rc_implementation->getFileDataByMD5Filename($hashed_filename);
    }

    /**
     * @param string|string[] $hashed_filename_or_filenames
     */
    public function unlinkFilesByMD5Filenames($hashed_filename_or_filenames): bool
    {
        return $this->rc_implementation->unlinkFilesByMD5Filenames($hashed_filename_or_filenames);
    }

    public function deliverFile(string $file): void
    {
        $this->rc_implementation->deliverFile($file);
    }

    public function deliverZipFile(): bool
    {
        return $this->rc_implementation->deliverZipFile();
    }

    public function importPath(string $path_to_file, int $posting_id): void
    {
        // Importing is only possible for IRSS based files
        $this->setPosId($posting_id);
        $this->rc_implementation->importFileToCollection($path_to_file, $this->getCurrentPosting());
    }
}
