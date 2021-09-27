<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class handles all operations on files for the forum object.
 * @author    Stefan Meyer <meyer@leifos.com>
 * @version   $Id$
 * @ingroup   ModulesForum
 */
class ilFileDataForum extends ilFileData
{
    const FORUM_PATH = 'forum';
    public int $obj_id;
    public int $pos_id;

    public string $forum_path;
    private mixed $error;

    public function __construct($a_obj_id = 0, $a_pos_id = 0)
    {
        global $DIC;
        $this->error = $DIC['ilErr'];

        parent::__construct();
        $this->forum_path = parent::getPath() . "/" . self::FORUM_PATH;

        // IF DIRECTORY ISN'T CREATED CREATE IT
        if (!$this->checkForumPath()) {
            $this->initDirectory();
        }
        $this->obj_id = (int) $a_obj_id;
        $this->pos_id = (int) $a_pos_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getPosId() : int
    {
        return $this->pos_id;
    }

    public function setPosId($a_id)
    {
        $this->pos_id = (int) $a_id;
    }

    public function getForumPath() : string
    {
        return $this->forum_path;
    }

    public function getFiles() : array
    {
        $files = [];

        foreach (new DirectoryIterator($this->forum_path) as $file) {
            /**
             * @var $file SplFileInfo
             */

            if ($file->isDir()) {
                continue;
            }

            list($obj_id, $rest) = explode('_', $file->getFilename(), 2);
            if ($obj_id == $this->obj_id) {
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

    public function getFilesOfPost() : array
    {
        $files = [];

        foreach (new DirectoryIterator($this->forum_path) as $file) {
            /**
             * @var $file SplFileInfo
             */

            if ($file->isDir()) {
                continue;
            }

            list($obj_id, $rest) = explode('_', $file->getFilename(), 2);
            if ($obj_id == $this->obj_id) {
                list($pos_id, $rest) = explode('_', $rest, 2);
                if ($pos_id == $this->getPosId()) {
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

    public function moveFilesOfPost(int $a_new_frm_id = 0) : bool
    {
        if ($a_new_frm_id) {
            foreach (new DirectoryIterator($this->forum_path) as $file) {
                /**
                 * @var $file SplFileInfo
                 */

                if ($file->isDir()) {
                    continue;
                }

                list($obj_id, $rest) = explode('_', $file->getFilename(), 2);
                if ($obj_id == $this->obj_id) {
                    list($pos_id, $rest) = explode('_', $rest, 2);
                    if ($pos_id == $this->getPosId()) {
                        \ilFileUtils::rename(
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

    public function ilClone($a_new_obj_id, $a_new_pos_id) : bool
    {
        foreach ($this->getFilesOfPost() as $file) {
            @copy(
                $this->getForumPath() . "/" . $this->obj_id . "_" . $this->pos_id . "_" . $file["name"],
                $this->getForumPath() . "/" . $a_new_obj_id . "_" . $a_new_pos_id . "_" . $file["name"]
            );
        }
        return true;
    }

    public function delete() : bool
    {
        foreach ($this->getFiles() as $file) {
            if (is_file($this->getForumPath() . "/" . $this->getObjId() . "_" . $file["name"])) {
                unlink($this->getForumPath() . "/" . $this->getObjId() . "_" . $file["name"]);
            }
        }
        return true;
    }

    public function storeUploadedFile(array $files) : bool
    {
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $index => $name) {
                // remove trailing '/'
                $name = rtrim($name, '/');

                $filename = ilUtil::_sanitizeFilemame($name);
                $temp_name = $files['tmp_name'][$index];
                $error = $files['error'][$index];

                if (strlen($filename) && strlen($temp_name) && $error == 0) {
                    $path = $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename;

                    $this->rotateFiles($path);
                    ilUtil::moveUploadedFile($temp_name, $filename, $path);
                }
            }

            return true;
        } elseif (isset($files['name']) && is_string($files['name'])) {
            // remove trailing '/'
            $files['name'] = rtrim($files['name'], '/');

            $filename = ilUtil::_sanitizeFilemame($files['name']);
            $temp_name = $files['tmp_name'];

            $path = $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename;

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
        if (is_file($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $a_filename)) {
            return unlink($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . "_" . $a_filename);
        }
        return true;
    }

    /**
     * get absolute path of filename
     * @param string relative path
     * @return string absolute path
     */
    public function getAbsolutePath($a_path) : string
    {
        return $this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . "_" . $a_path;
    }

    /**
     * get file data of a specific attachment
     * @param string md5 encrypted filename
     * @return array|false
     */
    public function getFileDataByMD5Filename($a_md5_filename) : array
    {
        $files = ilUtil::getDir($this->forum_path);
        foreach ($files as $file) {
            if ($file['type'] == 'file' && md5($file['entry']) == $a_md5_filename) {
                return [
                    'path' => $this->forum_path . '/' . $file['entry'],
                    'filename' => $file['entry'],
                    'clean_filename' => str_replace($this->obj_id . '_' . $this->pos_id . '_', '', $file['entry'])
                ];
            }
        }

        return false;
    }

    /**
     * get file data of a specific attachment
     * @param string|array md5 encrypted filename or array of multiple md5 encrypted files
     */
    public function unlinkFilesByMD5Filenames($a_md5_filename) : bool
    {
        $files = ilUtil::getDir($this->forum_path);
        if (is_array($a_md5_filename)) {
            foreach ($files as $file) {
                if ($file['type'] == 'file' && in_array(md5($file['entry']), $a_md5_filename)) {
                    unlink($this->forum_path . '/' . $file['entry']);
                }
            }

            return true;
        } else {
            foreach ($files as $file) {
                if ($file['type'] == 'file' && md5($file['entry']) == $a_md5_filename) {
                    return unlink($this->forum_path . '/' . $file['entry']);
                }
            }
        }

        return false;
    }

    public function checkFilesExist($a_files) : bool
    {
        if ($a_files) {
            foreach ($a_files as $file) {
                if (!is_file($this->forum_path . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $file)) {
                    return false;
                }
            }
            return true;
        }
        return true;
    }

    private function checkForumPath() : bool
    {
        if (!is_dir($this->getForumPath())) {
            return false;
        }
        $this->checkReadWrite();

        return true;
    }

    private function checkReadWrite() : bool
    {
        if (is_writable($this->forum_path) && is_readable($this->forum_path)) {
            return true;
        } else {
            $this->error->raiseError("Forum directory is not readable/writable by webserver", $this->error->FATAL);
        }
        return true;
    }

    private function initDirectory()
    {
        if (is_writable($this->getPath())) {
            if (mkdir($this->getPath() . '/' . self::FORUM_PATH)) {
                if (chmod($this->getPath() . '/' . self::FORUM_PATH, 0755)) {
                    $this->forum_path = $this->getPath() . '/' . self::FORUM_PATH;
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

    public function deliverFile($file)
    {
        global $DIC;

        if (!$path = $this->getFileDataByMD5Filename($file)) {
            return ilUtil::sendFailure($DIC->lanuage()->txt('error_reading_file'), true);
        } else {
            return ilUtil::deliverFile($path['path'], $path['clean_filename']);
        }
    }

    public function deliverZipFile() : bool
    {
        global $DIC;

        $zip_file = $this->createZipFile();
        if (!$zip_file) {
            ilUtil::sendFailure($DIC->language()->txt('error_reading_file'), true);
            return false;
        } else {
            $post = new ilForumPost($this->getPosId());
            ilUtil::deliverFile($zip_file, $post->getSubject() . '.zip', '', false, true, false);
            ilUtil::delDir($this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId());
            $DIC->http()->close();
        }
        return true;
    }

    protected function createZipFile() : ?string
    {
        $filesOfPost = $this->getFilesOfPost();
        ksort($filesOfPost);

        ilUtil::makeDirParents($this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId());
        $tmp_dir = $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId();
        foreach ($filesOfPost as $file) {
            @copy($file['path'], $tmp_dir . '/' . $file['name']);
        }

        $zip_file = null;
        if (ilUtil::zip($tmp_dir,
            $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId() . '.zip')) {
            $zip_file = $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId() . '.zip';
        }

        return $zip_file;
    }
}
