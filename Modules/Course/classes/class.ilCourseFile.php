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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @extends Object
 */
class ilCourseFile
{
    private int $course_id = 0;
    private int $file_id = 0;
    private string $file_name = '';
    private string $file_type = '';
    private int $file_size = 0;
    private string $tmp_name = '';
    private int $error_code = 0;

    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected ilErrorHandling $error;
    protected ?ilFSStorageCourse $fss_storage = null;

    public function __construct(int $a_file_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->error = $DIC['ilErr'];
        $this->file_id = $a_file_id;
        $this->__read();
    }

    public static function _cloneFiles(int $a_source_id, int $a_target_id) : void
    {
        $source = new ilFSStorageCourse($a_source_id);

        foreach (ilCourseFile::_readFilesByCourse($a_source_id) as $file_obj) {
            $new_file = new ilCourseFile();
            $new_file->setCourseId($a_target_id);
            $new_file->setFileName($file_obj->getFileName());
            $new_file->setFileSize($file_obj->getFileSize());
            $new_file->setFileType($file_obj->getFileType());
            $new_file->create(false);

            $target = new ilFSStorageCourse($a_target_id);
            $target->initInfoDirectory();
            $source->copyFile(
                $file_obj->getAbsolutePath(),
                $new_file->fss_storage->getInfoDirectory() . '/' . $new_file->getFileId()
            );
        }
    }

    public function setFileId(int $a_id) : void
    {
        $this->file_id = $a_id;
    }

    public function getFileId() : int
    {
        return $this->file_id;
    }

    public function getCourseId() : int
    {
        return $this->course_id;
    }

    public function setCourseId(int $a_course_id) : void
    {
        $this->course_id = $a_course_id;
    }

    public function setFileName(string $a_name) : void
    {
        $this->file_name = $a_name;
    }

    public function getFileName() : string
    {
        return $this->file_name;
    }

    public function setFileType(string $a_type) : void
    {
        $this->file_type = $a_type;
    }

    public function getFileType() : string
    {
        return $this->file_type;
    }

    public function setFileSize(int $a_size) : void
    {
        $this->file_size = $a_size;
    }

    public function getFileSize() : int
    {
        return $this->file_size;
    }

    public function setTemporaryName(string $a_name) : void
    {
        $this->tmp_name = $a_name;
    }

    public function getTemporaryName() : string
    {
        return $this->tmp_name;
    }

    public function setErrorCode(int $a_code) : void
    {
        $this->error_code = $a_code;
    }

    public function getErrorCode() : int
    {
        return $this->error_code;
    }

    public function getAbsolutePath() : string
    {
        // workaround for "secured" files.
        if (!$this->fss_storage instanceof \ilFSStorageCourse) {
            return '';
        }

        $file = $this->fss_storage->getInfoDirectory() . '/' . $this->getFileId();
        if (!file_exists($file)) {
            $file = $this->fss_storage->getInfoDirectory() . '/' . $this->getFileId() . '.sec';
        }
        if (file_exists($file)) {
            return $file;
        }
        return '';
    }

    public function getInfoDirectory() : string
    {
        if (is_object($this->fss_storage)) {
            return $this->fss_storage->getInfoDirectory();
        }
        return '';
    }

    public function validate() : bool
    {
        switch ($this->getErrorCode()) {
            case UPLOAD_ERR_INI_SIZE:
                $this->error->appendMessage($this->lng->txt('file_upload_ini_size'));
                return false;
            case UPLOAD_ERR_FORM_SIZE:
                $this->error->appendMessage($this->lng->txt('file_upload_form_size'));
                return false;

            case UPLOAD_ERR_PARTIAL:
                $this->error->appendMessage($this->lng->txt('file_upload_only_partial'));
                return false;

            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error->appendMessage($this->lng->txt('file_upload_no_tmp_dir'));
                return false;

            case UPLOAD_ERR_NO_FILE:
                return false;

            case UPLOAD_ERR_OK:
            default:
                return true;
        }
    }

    public function create(bool $a_upload = true) : bool
    {
        if ($this->getErrorCode() != 0) {
            return false;
        }
        $next_id = $this->db->nextId('crs_file');
        $query = "INSERT INTO crs_file (file_id,course_id,file_name,file_size,file_type) " .
            "VALUES( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getCourseId(), 'integer') . ", " .
            $this->db->quote($this->getFileName(), 'text') . ", " .
            $this->db->quote($this->getFileSize(), 'integer') . ", " .
            $this->db->quote($this->getFileType(), 'text') . " " .
            ")";
        $res = $this->db->manipulate($query);
        $this->setFileId($next_id);

        $this->fss_storage = new ilFSStorageCourse($this->getCourseId());
        $this->fss_storage->initInfoDirectory();

        if ($a_upload) {
            // now create file
            ilFileUtils::moveUploadedFile(
                $this->getTemporaryName(),
                $this->getFileName(),
                $this->fss_storage->getInfoDirectory() . '/' . $this->getFileId()
            );
        }
        return true;
    }

    public function delete() : void
    {
        // Delete db entry
        $query = "DELETE FROM crs_file " .
            "WHERE file_id = " . $this->db->quote($this->getFileId(), 'integer') . "";
        $res = $this->db->manipulate($query);

        // Delete file
        if (file_exists($this->getAbsolutePath())) {
            unlink($this->getAbsolutePath());
        }
    }

    public static function _deleteByCourse(int $a_course_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // delete all course ids and delete assigned files
        $query = "DELETE FROM crs_file " .
            "WHERE course_id = " . $ilDB->quote($a_course_id, 'integer') . "";
        $res = $ilDB->manipulate($query);
    }

    /**
     * @param int $a_course_id obj_id of course
     * @return ilCourseFile[]
     */
    public static function _readFilesByCourse(int $a_course_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM crs_file " .
            "WHERE course_id = " . $ilDB->quote($a_course_id, 'integer') . "";

        $res = $ilDB->query($query);
        $files = [];
        while ($row = $ilDB->fetchObject($res)) {
            $files[] = new ilCourseFile((int) $row->file_id);
        }
        return $files;
    }

    public function __read() : void
    {
        if (!$this->file_id) {
            return;
        }

        // read file data
        $query = "SELECT * FROM crs_file WHERE file_id = " . $this->db->quote($this->file_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $this->db->fetchObject($res)) {
            $this->setFileName((string) $row->file_name);
            $this->setFileSize((int) $row->file_size);
            $this->setFileType((string) $row->file_type);
            $this->setCourseId((int) $row->course_id);
        }
        $this->fss_storage = new ilFSStorageCourse($this->getCourseId());
    }
}
