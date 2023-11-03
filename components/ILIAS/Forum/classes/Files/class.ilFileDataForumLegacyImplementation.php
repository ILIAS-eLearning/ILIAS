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

/**
 * This class handles all operations on files for the forum object.
 * @author    Stefan Meyer <meyer@leifos.com>
 * @ingroup   ModulesForum
 */
class ilFileDataForumLegacyImplementation extends ilFileData implements ilFileDataForumInterface
{
    private const FORUM_PATH = 'forum';
    private string $forum_path;
    private ilErrorHandling $error;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(private int $obj_id = 0, private int $pos_id = 0)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->error = $DIC['ilErr'];

        parent::__construct();
        $this->forum_path = $this->getPath() . '/' . self::FORUM_PATH;

        if (!$this->checkForumPath()) {
            $this->initDirectory();
        }
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getPosId(): int
    {
        return $this->pos_id;
    }

    public function setPosId(int $posting_id): void
    {
        $this->pos_id = $posting_id;
    }

    public function getForumPath(): string
    {
        return $this->forum_path;
    }

    /**
     * @return array{path: string, md5: string, name: string, size: int, ctime: string}[]
     */
    public function getFiles(): array
    {
        $directory_iterator = new DirectoryIterator($this->forum_path);
        $filter_iterator = new RegexIterator($directory_iterator, "/^{$this->obj_id}_(.+)$/");

        $files = [];
        foreach ($filter_iterator as $file) {
            /** @var SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            [$obj_id, $rest] = explode('_', $file->getFilename(), 2);
            if ((int) $obj_id === $this->obj_id) {
                $files[] = [
                    'path' => $file->getPathname(),
                    'md5' => md5($this->obj_id . '_' . $rest),
                    'name' => $rest,
                    'size' => $file->getSize(),
                    'ctime' => date('Y-m-d H:i:s', $file->getCTime())
                ];
            }
        }

        return $files;
    }

    /**
     * @return array<string, array{path: string, md5: string, name: string, size: int, ctime: string}>
     */
    public function getFilesOfPost(): array
    {
        $directory_iterator = new DirectoryIterator($this->forum_path);
        $filter_iterator = new RegexIterator($directory_iterator, "/^{$this->obj_id}_{$this->getPosId()}_(.+)$/");

        $files = [];
        foreach ($filter_iterator as $file) {
            /** @var SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            [$obj_id, $pos_id, $rest] = explode('_', $file->getFilename(), 3);
            if ((int) $pos_id === $this->getPosId()) {
                $files[$rest] = [
                    'path' => $file->getPathname(),
                    'md5' => md5($this->obj_id . '_' . $this->pos_id . '_' . $rest),
                    'name' => $rest,
                    'size' => $file->getSize(),
                    'ctime' => date('Y-m-d H:i:s', $file->getCTime())
                ];
            }
        }

        return $files;
    }

    public function moveFilesOfPost(int $new_frm_id = 0): bool
    {
        if ($new_frm_id !== 0) {
            $directory_iterator = new DirectoryIterator($this->forum_path);
            $filter_iterator = new RegexIterator($directory_iterator, "/^{$this->obj_id}_(\d+)_(.+)$/");

            foreach ($filter_iterator as $file) {
                /** @var SplFileInfo $file */
                if (!$file->isFile()) {
                    continue;
                }

                [$obj_id, $pos_id, $rest] = explode('_', $file->getFilename(), 3);
                if ((int) $obj_id !== $this->obj_id || (int) $pos_id !== $this->getPosId()) {
                    continue;
                }

                ilFileUtils::rename(
                    $file->getPathname(),
                    $this->forum_path . '/' . $new_frm_id . '_' . $this->pos_id . '_' . $rest
                );
            }

            return true;
        }

        return false;
    }

    public function ilClone(int $new_obj_id, int $new_posting_id): bool
    {
        foreach ($this->getFilesOfPost() as $file) {
            copy(
                $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $file['name'],
                $this->getForumPath() . '/' . $new_obj_id . '_' . $new_posting_id . '_' . $file['name']
            );
        }
        return true;
    }

    public function delete(array $posting_ids_to_delete = null): bool
    {
        foreach ($this->getFiles() as $file) {
            if (is_file($this->getForumPath() . '/' . $this->getObjId() . '_' . $file['name'])) {
                unlink($this->getForumPath() . '/' . $this->getObjId() . '_' . $file['name']);
            }
        }

        return true;
    }

    public function storeUploadedFiles(): bool
    {
        throw new ilException('ilFileDataForumLegacyImplementation::storeUploadedFiles() is not implemented.');
    }

    public function unlinkFile(string $filename): bool
    {
        if (is_file($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename)) {
            return unlink($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename);
        }

        return false;
    }

    /**
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashed_filename): ?array
    {
        $files = ilFileUtils::getDir($this->forum_path);
        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashed_filename) {
                return [
                    'path' => $this->forum_path . '/' . $file['entry'],
                    'filename' => $file['entry'],
                    'clean_filename' => str_replace($this->obj_id . '_' . $this->pos_id . '_', '', $file['entry'])
                ];
            }
        }

        return null;
    }

    /**
     * @param string|string[] $hashed_filename_or_filenames
     */
    public function unlinkFilesByMD5Filenames($hashed_filename_or_filenames): bool
    {
        $files = ilFileUtils::getDir($this->forum_path);
        if (is_array($hashed_filename_or_filenames)) {
            foreach ($files as $file) {
                if ($file['type'] === 'file' && in_array(md5($file['entry']), $hashed_filename_or_filenames, true)) {
                    unlink($this->forum_path . '/' . $file['entry']);
                }
            }

            return true;
        }

        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashed_filename_or_filenames) {
                return unlink($this->forum_path . '/' . $file['entry']);
            }
        }

        return false;
    }

    private function checkForumPath(): bool
    {
        if (!is_dir($this->getForumPath())) {
            return false;
        }
        $this->checkReadWrite();

        return true;
    }

    private function checkReadWrite(): void
    {
        if (!is_writable($this->forum_path) || !is_readable($this->forum_path)) {
            $this->error->raiseError('Forum directory is not readable/writable by webserver', $this->error->FATAL);
        }
    }

    private function initDirectory(): void
    {
        if (is_writable($this->getPath()) && mkdir($this->getPath() . '/' . self::FORUM_PATH) && chmod(
            $this->getPath() . '/' . self::FORUM_PATH,
            0755
        )) {
            $this->forum_path = $this->getPath() . '/' . self::FORUM_PATH;
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
        global $DIC;

        if (($path = $this->getFileDataByMD5Filename($file)) !== null) {
            ilFileDelivery::deliverFileLegacy($path['path'], $path['clean_filename']);
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->lanuage()->txt('error_reading_file'), true);
        }
    }

    public function deliverZipFile(): bool
    {
        global $DIC;

        $zip_file = $this->createZipFile();
        if (!$zip_file) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('error_reading_file'), true);
            return false;
        }

        $post = new ilForumPost($this->getPosId());
        ilFileDelivery::deliverFileLegacy($zip_file, $post->getSubject() . '.zip', '', false, true, false);
        ilFileUtils::delDir($this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId());
        $DIC->http()->close();
        return true; // never
    }

    protected function createZipFile(): ?string
    {
        $filesOfPost = $this->getFilesOfPost();
        ksort($filesOfPost);

        ilFileUtils::makeDirParents($this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId());
        $tmp_dir = $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId();
        foreach ($filesOfPost as $file) {
            copy($file['path'], $tmp_dir . '/' . $file['name']);
        }

        $zip_file = null;
        if (ilFileUtils::zip(
            $tmp_dir,
            $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId() . '.zip'
        )) {
            $zip_file = $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId() . '.zip';
        }

        return $zip_file;
    }
}
