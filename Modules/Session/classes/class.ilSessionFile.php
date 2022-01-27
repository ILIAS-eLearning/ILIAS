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
 ********************************************************************
 */

/**
* class ilSessionFile
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*/
class ilSessionFile
{
    protected ilErrorHandling $ilErr;
    protected ilDBInterface $db;
    protected ilLanguage $lng;
    protected int $event_id = 0;
    protected int $file_id = 0;
    protected string $file_name = "";
    protected string $file_type = "";
    protected int $file_size = 0;
    protected string $tmp_name = "";
    protected int $error_code = 0;
    protected ilFSStorageSession $fss_storage;

    public function __construct(int $a_file_id = 0)
    {
        global $DIC;

        $this->ilErr = $DIC['ilErr'];
        $this->db = $DIC->database();
        $this->lng = $DIC->language();

        $this->file_id = $a_file_id;
        $this->__read();
    }

    public function setFileId(int $a_id) : void
    {
        $this->file_id = $a_id;
    }

    public function getFileId() : int
    {
        return $this->file_id;
    }

    public function getSessionId() : int
    {
        return $this->event_id;
    }

    public function setSessionId(int $a_event_id) : void
    {
        $this->event_id = $a_event_id;
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
        return $this->fss_storage->getAbsolutePath() . "/" . $this->getFileId();
    }

    public function validate() : bool
    {
        switch ($this->getErrorCode()) {
            case UPLOAD_ERR_INI_SIZE:
                $this->ilErr->appendMessage($this->lng->txt('file_upload_ini_size'));
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $this->ilErr->appendMessage($this->lng->txt('file_upload_form_size'));
                break;

            case UPLOAD_ERR_PARTIAL:
                $this->ilErr->appendMessage($this->lng->txt('file_upload_only_partial'));
                break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $this->ilErr->appendMessage($this->lng->txt('file_upload_no_tmp_dir'));
                break;

            case UPLOAD_ERR_OK:
            case UPLOAD_ERR_NO_FILE:
            default:
                return true;
        }

        return false;
    }

    public function cloneFiles(int $a_target_event_id) : void
    {
        $file = new ilSessionFile();
        $file->setSessionId($a_target_event_id);
        $file->setFileName($this->getFileName());
        $file->setFileType($this->getFileType());
        $file->setFileSize($this->getFileSize());
        $file->create(false);
        
        // Copy file
        $source = new ilFSStorageSession($this->getSessionId());
        $source->copyFile($this->getAbsolutePath(), $file->getAbsolutePath());
    }

    public function create(bool $a_upload = true) : bool
    {
        $ilDB = $this->db;
        
        if ($this->getErrorCode() != 0) {
            return false;
        }

        $next_id = $ilDB->nextId('event_file');
        $query = "INSERT INTO event_file (file_id,event_id,file_name,file_size,file_type) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $ilDB->quote($this->getSessionId(), 'integer') . ", " .
            $ilDB->quote($this->getFileName(), 'text') . ", " .
            $ilDB->quote($this->getFileSize(), 'integer') . ", " .
            $ilDB->quote($this->getFileType(), 'text') . " " .
            ")";
        
        $res = $ilDB->manipulate($query);
        $this->setFileId($next_id);

        $this->fss_storage = new ilFSStorageSession($this->getSessionId());
        $this->fss_storage->createDirectory();

        if ($a_upload) {
            // now create file
            ilFileUtils::moveUploadedFile(
                $this->getTemporaryName(),
                $this->getFileName(),
                $this->fss_storage->getAbsolutePath() . '/' . $this->getFileId()
            );
        }

        return true;
    }

    public function delete() : bool
    {
        $ilDB = $this->db;
        
        // Delete db entry
        $query = "DELETE FROM event_file " .
            "WHERE file_id = " . $ilDB->quote($this->getFileId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        // Delete file
        $this->fss_storage->deleteFile($this->getAbsolutePath());
        return true;
    }
        
    public function _deleteByEvent(int $a_event_id) : bool
    {
        $ilDB = $this->db;

        // delete all event ids and delete assigned files
        $query = "DELETE FROM event_file " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer');
        $res = $ilDB->manipulate($query);

        #$this->fss_storage->delete();
        return true;
    }

    public static function _readFilesByEvent(int $a_event_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM event_file " .
            "WHERE event_id = " . $ilDB->quote($a_event_id, 'integer');

        $res = $ilDB->query($query);
        $files = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $files[] = new ilSessionFile($row->file_id);
        }
        return $files;
    }

    protected function __read() : bool
    {
        $ilDB = $this->db;
        
        if (!$this->file_id) {
            return true;
        }

        // read file data
        $query = "SELECT * FROM event_file WHERE file_id = " . $ilDB->quote($this->file_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setFileName($row->file_name);
            $this->setFileSize($row->file_size);
            $this->setFileType($row->file_type);
            $this->setSessionId($row->event_id);
        }
        $this->fss_storage = new ilFSStorageSession($this->getSessionId());
        return true;
    }
}
