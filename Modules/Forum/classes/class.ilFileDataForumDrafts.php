<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class handles all operations on files for the drafts of a forum object.
 * @author    Nadia Matuschek <nmatuschek@databay.de>
 * @ingroup   ModulesForum
 */
class ilFileDataForumDrafts extends ilFileData
{
    protected int $obj_id = 0;
    protected int $draft_id = 0;
    protected string $drafts_path = '';
    private $lng;
    private $error;

    public function __construct($obj_id, $draft_id)
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->error = $DIC['ilErr'];

        $this->obj_id = (int) $obj_id;
        $this->draft_id = (int) $draft_id;

        parent::__construct();
        $this->drafts_path = parent::getPath() . "/forum/drafts";

        // IF DIRECTORY ISN'T CREATED CREATE IT
        if (!$this->checkForumDraftsPath()) {
            $this->initDirectory();
        }
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id)
    {
        $this->obj_id = $obj_id;
    }

    public function getDraftId() : int
    {
        return $this->draft_id;
    }

    public function setDraftId(int $draft_id)
    {
        $this->draft_id = $draft_id;
    }

    public function getDraftsPath() : string
    {
        return $this->drafts_path;
    }

    public function getFiles() : array
    {
        $files = [];

        foreach (new DirectoryIterator($this->getDraftsPath() . '/' . $this->getDraftId()) as $file) {
            /**
             * @var $file SplFileInfo
             */

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

    public function moveFilesOfDraft($forum_path, $new_post_id) : bool
    {
        foreach ($this->getFilesOfPost() as $file) {
            @copy(
                $file['path'],
                $forum_path . '/' . $this->obj_id . '_' . $new_post_id . '_' . $file['name']
            );
        }
        return true;
    }

    public function delete() : bool
    {
        ilUtil::delDir($this->getDraftsPath() . '/' . $this->getDraftId());
        return true;
    }

    public function storeUploadedFile(array $files) : bool
    {
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                // remove trailing '/'
                while (substr($name, -1) == '/') {
                    $name = substr($name, 0, -1);
                }
                $filename = ilUtil::_sanitizeFilemame($name);
                $temp_name = $files['tmp_name'][$index];
                $error = $files['error'][$index];

                if (strlen($filename) && strlen($temp_name) && $error == 0) {
                    $path = $this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $filename;

                    $this->rotateFiles($path);
                    ilUtil::moveUploadedFile($temp_name, $filename, $path);
                }
            }

            return true;
        } elseif (isset($files['name']) && is_string($files['name'])) {
            // remove trailing '/'
            while (substr($files['name'], -1) == '/') {
                $files['name'] = substr($files['name'], 0, -1);
            }
            $filename = ilUtil::_sanitizeFilemame($files['name']);
            $temp_name = $files['tmp_name'];

            $path = $this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $filename;

            $this->rotateFiles($path);
            ilUtil::moveUploadedFile($temp_name, $filename, $path);

            return true;
        }

        return false;
    }

    public function unlinkFiles($a_filenames) : string
    {
        if (is_array($a_filenames)) {
            foreach ($a_filenames as $file) {
                if (!$this->unlinkFile($file)) {
                    return $file;
                }
            }
        }
        return '';
    }

    public function unlinkFile($a_filename) : bool
    {
        if (is_file($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $a_filename)) {
            return unlink($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $a_filename);
        }
        return false;
    }

    public function getAbsolutePath($a_path)
    {
        return $this->getDraftsPath() . '/' . $this->getDraftId();
    }

    public function getFileDataByMD5Filename($a_md5_filename) : array
    {
        $files = ilUtil::getDir($this->getDraftsPath() . '/' . $this->getDraftId());
        foreach ($files as $file) {
            if ($file['type'] == 'file' && md5($file['entry']) == $a_md5_filename) {
                return [
                    'path' => $this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file['entry'],
                    'filename' => $file['entry'],
                    'clean_filename' => $file['entry']
                ];
            }
        }

        return [];
    }

    public function unlinkFilesByMD5Filenames($a_md5_filename) : bool
    {
        $files = ilUtil::getDir($this->getDraftsPath() . '/' . $this->getDraftId());
        if (is_array($a_md5_filename)) {
            foreach ($files as $file) {
                if ($file['type'] == 'file' && in_array(md5($file['entry']), $a_md5_filename)) {
                    unlink($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file['entry']);
                }
            }

            return true;
        } else {
            foreach ($files as $file) {
                if ($file['type'] == 'file' && md5($file['entry']) == $a_md5_filename) {
                    return unlink($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file['entry']);
                }
            }
        }

        return false;
    }

    public function checkFilesExist($a_files) : bool
    {
        if ($a_files) {
            foreach ($a_files as $file) {
                if (!is_file($this->getDraftsPath() . '/' . $this->getDraftId() . '/' . $file)) {
                    return false;
                }
            }
            return true;
        }
        return true;
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
        if (is_writable($this->getDraftsPath() . '/' . $this->getDraftId()) && is_readable($this->getDraftsPath() . '/' . $this->getDraftId())) {
            return;
        } else {
            $this->error->raiseError("Forum directory is not readable/writable by webserver", $this->error->FATAL);
        }
    }

    private function initDirectory() : void
    {
        if (is_writable($this->getPath())) {
            if (ilUtil::makeDirParents($this->getDraftsPath() . "/" . $this->getDraftId())) {
                if (chmod($this->getDraftsPath() . "/" . $this->getDraftId(), 0755)) {
                    return;
                }
            }
        }
    }

    private function rotateFiles($a_path) : bool
    {
        if (is_file($a_path)) {
            $this->rotateFiles($a_path . ".old");
            return \ilFileUtils::rename($a_path, $a_path . '.old');
        }
        return true;
    }

    /**
     * @param $file $_GET['file']
     * @return bool|void
     */
    public function deliverFile($file)
    {
        if (!$path = $this->getFileDataByMD5Filename($file)) {
            return ilUtil::sendFailure($this->lng->txt('error_reading_file'), true);
        } else {
            return ilUtil::deliverFile($path['path'], $path['clean_filename']);
        }
    }

    public function deliverZipFile()
    {
        $zip_file = $this->createZipFile();
        if (!$zip_file) {
            ilUtil::sendFailure($this->lng->txt('error_reading_file'), true);
            return false;
        } else {
            $post = ilForumPostDraft::newInstanceByDraftId($this->getDraftId());
            ilUtil::deliverFile($zip_file, $post->getPostSubject() . '.zip', '', false, true, false);
            ilUtil::delDir($this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId());
            exit();
        }
    }

    public function createZipFile() : ?string
    {
        $filesOfDraft = $this->getFilesOfPost();
        ilUtil::makeDirParents($this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId());
        $tmp_dir = $this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId();

        if (count($filesOfDraft)) {
            ksort($filesOfDraft);

            foreach ($filesOfDraft as $file) {
                @copy($file['path'], $tmp_dir . '/' . $file['name']);
            }
        }

        $zip_file = null;
        if (ilUtil::zip($tmp_dir, $this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId() . '.zip')) {
            $zip_file = $this->getDraftsPath() . '/drafts_zip/' . $this->getDraftId() . '.zip';
        }

        return $zip_file;
    }
}
