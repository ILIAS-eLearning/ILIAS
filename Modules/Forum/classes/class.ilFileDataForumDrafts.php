<?php declare(strict_types=1);

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
 * This class handles all operations on files for the drafts of a forum object.
 * @author    Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup   ModulesForum
 */
class ilFileDataForumDrafts extends ilFileData
{
    private int $obj_id;
    private int $draft_id;
    private string $drafts_path;
    private ilLanguage $lng;
    private ilErrorHandling $error;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct(int $obj_id, int $draft_id)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng = $DIC->language();
        $this->error = $DIC['ilErr'];

        $this->obj_id = $obj_id;
        $this->draft_id = $draft_id;

        parent::__construct();
        $this->drafts_path = $this->getPath() . '/forum/drafts';

        if (!$this->checkForumDraftsPath()) {
            $this->initDirectory();
        }
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id) : void
    {
        $this->obj_id = $obj_id;
    }

    public function getDraftId() : int
    {
        return $this->draft_id;
    }

    public function setDraftId(int $draft_id) : void
    {
        $this->draft_id = $draft_id;
    }

    public function getDraftsPath() : string
    {
        return $this->drafts_path;
    }

    /**
     * @return array{path: string, md5: string, name: string, size: int, ctime: string}[]
     */
    public function getFiles() : array
    {
        $files = [];

        foreach (new DirectoryIterator($this->getDraftsPath() . '/' . $this->getDraftId()) as $file) {
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
    public function getFilesOfPost() : array
    {
        $files = [];

        foreach (new DirectoryIterator($this->getDraftsPath() . '/' . $this->getDraftId()) as $file) {
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

    public function moveFilesOfDraft(string $forum_path, int $new_post_id) : bool
    {
        foreach ($this->getFilesOfPost() as $file) {
            copy(
                $file['path'],
                $forum_path . '/' . $this->obj_id . '_' . $new_post_id . '_' . $file['name']
            );
        }

        return true;
    }

    public function delete() : bool
    {
        ilFileUtils::delDir($this->getDraftsPath() . '/' . $this->getDraftId());
        return true;
    }

    public function storeUploadedFile(array $files) : bool
    {
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                $name = rtrim($name, '/');
                $filename = ilFileUtils::_sanitizeFilemame($name);
                $temp_name = $files['tmp_name'][$index];
                $error = $files['error'][$index];

                if ($filename !== '' && $temp_name !== '' && (int) $error === 0) {
                    $path = $this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $filename;

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

            $path = $this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $filename;

            $this->rotateFiles($path);
            ilFileUtils::moveUploadedFile($temp_name, $filename, $path);

            return true;
        }

        return false;
    }

    public function unlinkFile(string $a_filename) : bool
    {
        if (is_file($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $a_filename)) {
            return unlink($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $a_filename);
        }

        return false;
    }

    /**
     * @param string $hashedFilename
     * @return array{path: string, filename: string, clean_filename: string}|null
     */
    public function getFileDataByMD5Filename(string $hashedFilename) : ?array
    {
        $files = ilFileUtils::getDir($this->getDraftsPath() . '/' . $this->getDraftId());
        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashedFilename) {
                return [
                    'path' => $this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file['entry'],
                    'filename' => $file['entry'],
                    'clean_filename' => $file['entry']
                ];
            }
        }

        return null;
    }

    /**
     * @param string|string[] $hashedFilenameOrFilenames
     * @return bool
     */
    public function unlinkFilesByMD5Filenames($hashedFilenameOrFilenames) : bool
    {
        $files = ilFileUtils::getDir($this->getDraftsPath() . '/' . $this->getDraftId());
        if (is_array($hashedFilenameOrFilenames)) {
            foreach ($files as $file) {
                if ($file['type'] === 'file' && in_array(md5($file['entry']), $hashedFilenameOrFilenames, true)) {
                    unlink($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file['entry']);
                }
            }

            return true;
        }

        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $hashedFilenameOrFilenames) {
                return unlink($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file['entry']);
            }
        }

        return false;
    }

    public function checkForumDraftsPath() : bool
    {
        if (!is_dir($this->getDraftsPath() . '/' . $this->getDraftId())) {
            return false;
        }
        $this->checkReadWrite();

        return true;
    }

    private function checkReadWrite() : void
    {
        if (
            !is_writable($this->getDraftsPath() . '/' . $this->getDraftId()) ||
            !is_readable($this->getDraftsPath() . '/' . $this->getDraftId())
        ) {
            $this->error->raiseError('Forum directory is not readable/writable by webserver', $this->error->FATAL);
        }
    }

    private function initDirectory() : void
    {
        if (is_writable($this->getPath()) && ilFileUtils::makeDirParents($this->getDraftsPath() . '/' . $this->getDraftId()) && chmod(
            $this->getDraftsPath() . '/' . $this->getDraftId(),
            0755
        )) {
            // Empty, whyever @nmatuschek?
        }
    }

    private function rotateFiles(string $a_path) : void
    {
        if (is_file($a_path)) {
            $this->rotateFiles($a_path . '.old');
            ilFileUtils::rename($a_path, $a_path . '.old');
        }
    }

    public function deliverFile(string $file) : void
    {
        if (($path = $this->getFileDataByMD5Filename($file)) !== null) {
            ilFileDelivery::deliverFileLegacy($path['path'], $path['clean_filename']);
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('error_reading_file'), true);
        }
    }

    public function deliverZipFile() : bool
    {
        global $DIC;

        $zip_file = $this->createZipFile();
        if (!$zip_file) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng->txt('error_reading_file'), true);
            return false;
        }

        $post = ilForumPostDraft::newInstanceByDraftId($this->getDraftId());
        ilFileDelivery::deliverFileLegacy($zip_file, $post->getPostSubject() . '.zip', '', false, true, false);
        ilFileUtils::delDir($this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId());
        $DIC->http()->close();
        return true; // never
    }

    public function createZipFile() : ?string
    {
        $filesOfDraft = $this->getFilesOfPost();
        ilFileUtils::makeDirParents($this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId());
        $tmp_dir = $this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId();

        if (count($filesOfDraft)) {
            ksort($filesOfDraft);

            foreach ($filesOfDraft as $file) {
                copy($file['path'], $tmp_dir . '/' . $file['name']);
            }
        }

        $zip_file = null;
        if (ilFileUtils::zip($tmp_dir, $this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId() . '.zip')) {
            $zip_file = $this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId() . '.zip';
        }

        return $zip_file;
    }
}
