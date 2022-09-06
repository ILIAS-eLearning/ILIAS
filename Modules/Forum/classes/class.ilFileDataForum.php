<?php

declare(strict_types=1);

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
class ilFileDataForum extends ilFileData
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

    public function setPosId(int $a_id): void
    {
        $this->pos_id = $a_id;
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
        $files = [];

        foreach (new DirectoryIterator($this->forum_path) as $file) {
            /** @var $file SplFileInfo */
            if ($file->isDir()) {
                continue;
            }

            [$obj_id, $rest] = explode('_', $file->getFilename(), 2);
            if ((int) $obj_id === $this->obj_id) {
                $files[] = [
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

    /**
     * @return array<string, array{path: string, md5: string, name: string, size: int, ctime: string}>
     */
    public function getFilesOfPost(): array
    {
        $files = [];

        foreach (new DirectoryIterator($this->forum_path) as $file) {
            /** @var $file SplFileInfo */
            if ($file->isDir()) {
                continue;
            }

            [$obj_id, $rest] = explode('_', $file->getFilename(), 2);
            if ((int) $obj_id === $this->obj_id) {
                [$pos_id, $rest] = explode('_', $rest, 2);
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
        }

        return $files;
    }

    public function moveFilesOfPost(int $a_new_frm_id = 0): bool
    {
        if ($a_new_frm_id !== 0) {
            foreach (new DirectoryIterator($this->forum_path) as $file) {
                /** @var $file SplFileInfo */
                if ($file->isDir()) {
                    continue;
                }

                [$obj_id, $rest] = explode('_', $file->getFilename(), 2);
                if ((int) $obj_id === $this->obj_id) {
                    [$pos_id, $rest] = explode('_', $rest, 2);
                    if ((int) $pos_id === $this->getPosId()) {
                        ilFileUtils::rename(
                            $file->getPathname(),
                            $this->forum_path . '/' . $a_new_frm_id . '_' . $this->pos_id . '_' . $rest
                        );
                    }
                }
            }

            return true;
        }

        return false;
    }

    public function ilClone(int $a_new_obj_id, int $a_new_pos_id): bool
    {
        foreach ($this->getFilesOfPost() as $file) {
            copy(
                $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $file['name'],
                $this->getForumPath() . '/' . $a_new_obj_id . '_' . $a_new_pos_id . '_' . $file['name']
            );
        }
        return true;
    }

    public function delete(): bool
    {
        foreach ($this->getFiles() as $file) {
            if (is_file($this->getForumPath() . '/' . $this->getObjId() . '_' . $file['name'])) {
                unlink($this->getForumPath() . '/' . $this->getObjId() . '_' . $file['name']);
            }
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
                    $path = $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename;

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

            $path = $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename;

            $this->rotateFiles($path);
            ilFileUtils::moveUploadedFile($temp_name, $filename, $path);

            return true;
        }

        return false;
    }

    public function unlinkFile(string $a_filename): bool
    {
        if (is_file($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $a_filename)) {
            return unlink($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $a_filename);
        }

        return false;
    }

    /**
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashedFilename): ?array
    {
        $files = ilFileUtils::getDir($this->forum_path);
        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashedFilename) {
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
     * @param string|string[] $hashedFilenameOrFilenames
     */
    public function unlinkFilesByMD5Filenames($hashedFilenameOrFilenames): bool
    {
        $files = ilFileUtils::getDir($this->forum_path);
        if (is_array($hashedFilenameOrFilenames)) {
            foreach ($files as $file) {
                if ($file['type'] === 'file' && in_array(md5($file['entry']), $hashedFilenameOrFilenames, true)) {
                    unlink($this->forum_path . '/' . $file['entry']);
                }
            }

            return true;
        }

        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashedFilenameOrFilenames) {
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
