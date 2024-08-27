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

class ilFileDataForumDraftsLegacyImplementation extends ilFileData implements ilFileDataForumInterface
{
    private string $drafts_path;
    private ilLanguage $lng;
    private ilErrorHandling $error;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(private readonly int $obj_id, private readonly int $draft_id)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->error = $DIC['ilErr'];

        parent::__construct();
        $this->drafts_path = $this->getPath() . '/forum/drafts';

        if (!$this->checkForumDraftsPath()) {
            $this->initDirectory();
        }
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    private function getDraftsPath(): string
    {
        return $this->drafts_path;
    }

    /**
     * @return array{path: string, md5: string, name: string, size: int, ctime: string}[]
     */
    public function getFiles(): array
    {
        $files = [];

        foreach (new DirectoryIterator($this->getDraftsPath() . '/' . $this->draft_id) as $file) {
            /** @var $file SplFileInfo */
            if ($file->isDir()) {
                continue;
            }

            $files[] = [
                'path' => $file->getPathname(),
                'md5' => md5($file->getFilename()),
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'ctime' => date('Y-m-d H:i:s', $file->getCTime())
            ];
        }

        return $files;
    }

    /**
     * @return array<string, array{path: string, md5: string, name: string, size: int, ctime: string}>
     */
    public function getFilesOfPost(): array
    {
        $files = [];

        foreach (new DirectoryIterator($this->getDraftsPath() . '/' . $this->draft_id) as $file) {
            /**
             * @var $file SplFileInfo
             */

            if ($file->isDir()) {
                continue;
            }

            $files[$file->getFilename()] = [
                'path' => $file->getPathname(),
                'md5' => md5($file->getFilename()),
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'ctime' => date('Y-m-d H:i:s', $file->getCTime())
            ];
        }

        return $files;
    }

    public function delete(array $posting_ids_to_delete = null): bool
    {
        // Each element of $posting_ids_to_delete represents a "Draft Id", NOT a "Posting Id"
        if ($posting_ids_to_delete === null) {
            return true;
        }

        foreach ($posting_ids_to_delete as $draft_id) {
            ilFileUtils::delDir($this->getDraftsPath() . '/' . $draft_id);
        }

        return true;
    }

    public function storeUploadedFile(array $files): bool
    {
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                $name = rtrim($name, '/');
                $filename = ilFileUtils::_sanitizeFilemame($name);
                $temp_name = $files['tmp_name'][$index];
                $error = $files['error'][$index];

                if ($filename !== '' && $temp_name !== '' && (int) $error === 0) {
                    $path = $this->getDraftsPath() . '/' . $this->draft_id . '/' . $filename;

                    $this->rotateFiles($path);
                    ilFileUtils::moveUploadedFile($temp_name, $filename, $path);
                }
            }

            return true;
        }

        if (isset($files['name']) && is_string($files['name'])) {
            $files['name'] = rtrim($files['name'], '/');
            $filename = ilFileUtils::_sanitizeFilemame($files['name']);
            $temp_name = $files['tmp_name'];

            $path = $this->getDraftsPath() . '/' . $this->draft_id . '/' . $filename;

            $this->rotateFiles($path);
            ilFileUtils::moveUploadedFile($temp_name, $filename, $path);

            return true;
        }

        return false;
    }

    public function unlinkFile(string $filename): bool
    {
        throw new DomainException('Not implemented');
    }

    /**
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashed_filename): ?array
    {
        $files = ilFileUtils::getDir($this->getDraftsPath() . '/' . $this->draft_id);
        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashed_filename) {
                return [
                    'path' => $this->getDraftsPath() . '/' . $this->draft_id . '/' . $file['entry'],
                    'filename' => $file['entry'],
                    'clean_filename' => $file['entry']
                ];
            }
        }

        return null;
    }

    /**
     * @param string|string[] $hashedFilenameOrFilenames
     */
    public function unlinkFilesByMD5Filenames($hashedFilenameOrFilenames): bool
    {
        $files = ilFileUtils::getDir($this->getDraftsPath() . '/' . $this->draft_id);
        if (is_array($hashedFilenameOrFilenames)) {
            foreach ($files as $file) {
                if ($file['type'] === 'file' && in_array(md5($file['entry']), $hashedFilenameOrFilenames, true)) {
                    unlink($this->getDraftsPath() . '/' . $this->draft_id . '/' . $file['entry']);
                }
            }

            return true;
        }

        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashedFilenameOrFilenames) {
                return unlink($this->getDraftsPath() . '/' . $this->draft_id . '/' . $file['entry']);
            }
        }

        return false;
    }

    public function checkForumDraftsPath(): bool
    {
        if (!is_dir($this->getDraftsPath() . '/' . $this->draft_id)) {
            return false;
        }
        $this->checkReadWrite();

        return true;
    }

    private function checkReadWrite(): void
    {
        if (!is_writable($this->getDraftsPath() . '/' . $this->draft_id) ||
            !is_readable($this->getDraftsPath() . '/' . $this->draft_id)) {
            $this->error->raiseError('Forum directory is not readable/writable by webserver', $this->error->FATAL);
        }
    }

    private function initDirectory(): void
    {
        if (is_writable($this->getPath()) && ilFileUtils::makeDirParents(
            $this->getDraftsPath() . '/' . $this->draft_id
        ) && chmod(
            $this->getDraftsPath() . '/' . $this->draft_id,
            0755
        )) {
            // Empty, whyever @nmatuschek?
        }
    }

    private function rotateFiles(string $a_path): void
    {
        if (is_file($a_path)) {
            $this->rotateFiles($a_path . '.old');
            ilFileUtils::rename($a_path, $a_path . '.old');
        }
    }

    public function deliverFile(string $file): void
    {
        if (($path = $this->getFileDataByMD5Filename($file)) !== null) {
            ilFileDelivery::deliverFileLegacy($path['path'], $path['clean_filename']);
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('error_reading_file'), true);
        }
    }

    public function deliverZipFile(): bool
    {
        global $DIC;

        $zip_file = $this->createZipFile();
        if (!$zip_file) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('error_reading_file'), true);
            return false;
        }

        $post = ilForumPostDraft::newInstanceByDraftId($this->draft_id);
        ilFileDelivery::deliverFileLegacy($zip_file, $post->getPostSubject() . '.zip', '', false, true, false);
        ilFileUtils::delDir($this->getDraftsPath() . '/drafts_zip/' . $this->draft_id);
        $DIC->http()->close();

        return true; // never
    }

    private function createZipFile(): ?string
    {
        $filesOfDraft = $this->getFilesOfPost();
        ilFileUtils::makeDirParents($this->getDraftsPath() . '/drafts_zip/' . $this->draft_id);
        $tmp_dir = $this->getDraftsPath() . '/drafts_zip/' . $this->draft_id;

        if ($filesOfDraft !== []) {
            ksort($filesOfDraft);

            foreach ($filesOfDraft as $file) {
                copy($file['path'], $tmp_dir . '/' . $file['name']);
            }
        }

        $zip_file = null;
        if (ilFileUtils::zip($tmp_dir, $this->getDraftsPath() . '/drafts_zip/' . $this->draft_id . '.zip')) {
            $zip_file = $this->getDraftsPath() . '/drafts_zip/' . $this->draft_id . '.zip';
        }

        return $zip_file;
    }

    public function getPosId(): int
    {
        return $this->draft_id;
    }

    public function setPosId(int $posting_id): void
    {
    }

    public function getForumPath(): string
    {
        return '';
    }

    public function moveFilesOfPost(int $new_frm_id = 0): bool
    {
        return true;
    }

    public function ilClone(int $new_obj_id, int $new_posting_id): bool
    {
        return true;
    }

    public function storeUploadedFiles(): bool
    {
        return true;
    }
}
