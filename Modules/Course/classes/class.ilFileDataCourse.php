<?php declare(strict_types=0);
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
 * This class handles all operations of archive files for the course object
 * @author    Stefan Meyer <meyer@leifos.com>
 */
class ilFileDataCourse extends ilFileData
{
    private string $course_path;
    private int $course_id;

    protected ilErrorHandling $error;

    /**
     * @inheritDoc
     */
    public function __construct(int $a_course_id)
    {
        global $DIC;

        $this->error = $DIC['ilErr'];

        define('COURSE_PATH', 'course');

        parent::__construct();
        $this->course_path = parent::getPath() . "/" . COURSE_PATH;
        $this->course_id = $a_course_id;

        if (!$this->__checkPath()) {
            $this->__initDirectory();
        }
        $this->__checkImportPath();
    }

    public function getArchiveFile($a_rel_name) : string
    {
        if (file_exists($this->course_path . '/' . $a_rel_name . '.zip')) {
            return $this->course_path . '/' . $a_rel_name . '.zip';
        }
        if (file_exists($this->course_path . '/' . $a_rel_name . '.pdf')) {
            return $this->course_path . '/' . $a_rel_name . '.pdf';
        }
        return '';
    }

    public function getMemberExportFiles() : array
    {
        $files = array();
        $dp = opendir($this->course_path);

        while ($file = readdir($dp)) {
            if (is_dir($file)) {
                continue;
            }

            if (preg_match(
                "/^([0-9]{10})_[a-zA-Z]*_export_([a-z]+)_([0-9]+)\.[a-z]+$/",
                $file,
                $matches
            ) && $matches[3] == $this->course_id) {
                $timest = $matches[1];
                $file_info['name'] = $matches[0];
                $file_info['timest'] = $matches[1];
                $file_info['type'] = $matches[2];
                $file_info['id'] = $matches[3];
                $file_info['size'] = filesize($this->course_path . '/' . $file);

                $files[$timest] = $file_info;
            }
        }
        closedir($dp);
        return $files;
    }

    public function deleteMemberExportFile(string $a_name) : void
    {
        $file_name = $this->course_path . '/' . $a_name;
        if (file_exists($file_name)) {
            unlink($file_name);
        }
    }

    public function getMemberExportFile(string $a_name) : string
    {
        $file_name = $this->course_path . '/' . $a_name;
        if (file_exists($file_name)) {
            return file_get_contents($file_name);
        }
        return '';
    }

    public function deleteArchive(string $a_rel_name) : void
    {
        $this->deleteZipFile($this->course_path . '/' . $a_rel_name . '.zip');
        $this->deleteDirectory($this->course_path . '/' . $a_rel_name);
        $this->deleteDirectory(CLIENT_WEB_DIR . '/courses/' . $a_rel_name);
        $this->deletePdf($this->course_path . '/' . $a_rel_name . '.pdf');
    }

    public function deleteZipFile(string $a_abs_name) : bool
    {
        if (file_exists($a_abs_name)) {
            unlink($a_abs_name);
            return true;
        }
        return false;
    }

    public function deleteDirectory(string $a_abs_name) : bool
    {
        if (file_exists($a_abs_name)) {
            ilFileUtils::delDir($a_abs_name);

            return true;
        }
        return false;
    }

    public function deletePdf(string $a_abs_name) : bool
    {
        if (file_exists($a_abs_name)) {
            unlink($a_abs_name);

            return true;
        }
        return false;
    }

    public function copy(string $a_from, string $a_to) : bool
    {
        if (file_exists($a_from)) {
            copy($a_from, $this->getCoursePath() . '/' . $a_to);

            return true;
        }
        return false;
    }

    public function rCopy(string $a_from, string $a_to) : bool
    {
        ilFileUtils::rCopy($a_from, $this->getCoursePath() . '/' . $a_to);
        return true;
    }

    public function addDirectory(string $a_rel_name) : bool
    {
        ilFileUtils::makeDir($this->getCoursePath() . '/' . $a_rel_name);
        return true;
    }

    public function writeToFile(string $a_data, string $a_rel_name) : bool
    {
        if (!$fp = fopen($this->getCoursePath() . '/' . $a_rel_name, 'w+')) {
            die("Cannot open file: " . $this->getCoursePath() . '/' . $a_rel_name);
        }
        fwrite($fp, $a_data);
        return true;
    }

    public function zipFile(string $a_rel_name, string $a_zip_name) : int
    {
        ilFileUtils::zip($this->getCoursePath() . '/' . $a_rel_name, $this->getCoursePath() . '/' . $a_zip_name);

        // RETURN filesize
        return (int) filesize($this->getCoursePath() . '/' . $a_zip_name);
    }

    public function getCoursePath() : string
    {
        return $this->course_path;
    }

    public function createOnlineVersion(string $a_rel_name) : bool
    {
        ilFileUtils::makeDir(CLIENT_WEB_DIR . '/courses/' . $a_rel_name);
        ilFileUtils::rCopy($this->getCoursePath() . '/' . $a_rel_name, CLIENT_WEB_DIR . '/courses/' . $a_rel_name);
        return true;
    }

    public function getOnlineLink(string $a_rel_name) : string
    {
        return ilFileUtils::getWebspaceDir('filesystem') . '/courses/' . $a_rel_name . '/index.html';
    }

    public function __checkPath() : bool
    {
        if (!file_exists($this->getCoursePath())) {
            return false;
        }
        if (!file_exists(CLIENT_WEB_DIR . '/courses')) {
            ilFileUtils::makeDir(CLIENT_WEB_DIR . '/courses');
        }

        $this->__checkReadWrite();

        return true;
    }

    public function __checkImportPath() : void
    {
        if (!file_exists($this->getCoursePath() . '/import')) {
            ilFileUtils::makeDir($this->getCoursePath() . '/import');
        }

        if (!is_writable($this->getCoursePath() . '/import') || !is_readable($this->getCoursePath() . '/import')) {
            $this->error->raiseError("Course import path is not readable/writable by webserver", $this->error->FATAL);
        }
    }

    public function __checkReadWrite() : bool
    {
        if (is_writable($this->course_path) && is_readable($this->course_path)) {
            return true;
        } else {
            $this->error->raiseError("Course directory is not readable/writable by webserver", $this->error->FATAL);
        }
        return false;
    }

    public function __initDirectory() : bool
    {
        if (is_writable($this->getPath())) {
            ilFileUtils::makeDir($this->getPath() . '/' . COURSE_PATH);
            $this->course_path = $this->getPath() . '/' . COURSE_PATH;
            return true;
        }
        return false;
    }
}
