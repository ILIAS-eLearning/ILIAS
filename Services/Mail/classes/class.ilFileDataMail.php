<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* This class handles all operations on files (attachments) in directory ilias_data/mail
*
* @author	Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/

use ILIAS\Filesystem\Filesystem;

require_once("./Services/FileSystem/classes/class.ilFileData.php");
require_once("./Services/Utilities/classes/class.ilFileUtils.php");

/**
 * Class ilFileDataMail
 */
class ilFileDataMail extends ilFileData
{
    /**
    * user id
    * @var integer user_id
    * @access private
    */
    public $user_id;

    /**
    * path of mail directory
    * @var string path
    * @access private
    */
    public $mail_path;

    /**
     * @var
     */
    protected $mail_max_upload_file_size;

    /** @var Filesystem */
    protected $tmpDirectory;

    /** @var Filesystem */
    protected $storageDirectory;

    /** @var \ilDBInterface */
    protected $db;

    /**
    * Constructor
    * call base constructors
    * checks if directory is writable and sets the optional user_id
    * @param integereger user_id
    * @access	public
    */
    public function __construct($a_user_id = 0)
    {
        global $DIC;

        define('MAILPATH', 'mail');
        parent::__construct();
        $this->mail_path = parent::getPath() . "/" . MAILPATH;
        $this->checkReadWrite();
        $this->user_id = $a_user_id;

        $this->db = $DIC->database();
        $this->tmpDirectory = $DIC->filesystem()->temp();
        $this->storageDirectory = $DIC->filesystem()->storage();

        $this->initAttachmentMaxUploadSize();
    }

    /**
    * init directory
    * overwritten method
    * @access	public
    * @return string path
    */
    public function initDirectory()
    {
        if (is_writable($this->getPath())) {
            if (mkdir($this->getPath() . '/' . MAILPATH)) {
                if (chmod($this->getPath() . '/' . MAILPATH, 0755)) {
                    $this->mail_path = $this->getPath() . '/' . MAILPATH;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getUploadLimit()
    {
        return $this->mail_max_upload_file_size;
    }

    /**
     * @return float|null
     */
    public function getAttachmentsTotalSizeLimit()
    {
        $max_size = $this->ilias->getSetting('mail_maxsize_attach', '');
        if (!strlen($max_size)) {
            return null;
        }

        return (float) $this->ilias->getSetting('mail_maxsize_attach', 0) * 1024;
    }

    /**
    * get mail path
    * @access	public
    * @return string path
    */
    public function getMailPath()
    {
        return $this->mail_path;
    }

    /**
     * @return string
     */
    public function getAbsoluteAttachmentPoolPathPrefix() : string
    {
        return $this->mail_path . '/' . $this->user_id . '_';
    }

    /**
     * @param string $md5FileHash
     * @param int $mailId
     * @return array array An array containing the 'path' and the 'filename' for the passed MD5 hash
     * @throws \OutOfBoundsException
     */
    public function getAttachmentPathAndFilenameByMd5Hash(string $md5FileHash, int $mailId) : array
    {
        $res = $this->db->queryF(
            "SELECT path FROM mail_attachment WHERE mail_id = %s",
            ['integer'],
            [$mailId]
        );

        if (1 !== (int) $this->db->numRows($res)) {
            throw new \OutOfBoundsException();
        }

        $row = $this->db->fetchAssoc($res);

        $relativePath = $row['path'];
        $path = $this->getMailPath() . '/' . $row['path'];

        $files = ilUtil::getDir($path);
        foreach ($files as $file) {
            if ($file['type'] === 'file' && md5($file['entry']) === $md5FileHash) {
                return [
                    'path' => $this->getMailPath() . '/' . $relativePath . '/' . $file['entry'],
                    'filename' => $file['entry']
                ];
            }
        }

        throw new \OutOfBoundsException();
    }

    /**
     * @param int $mailId
     * @return string
     */
    private function getAttachmentPathByMailId(int $mailId) : string
    {
        $query = $this->db->query(
            "SELECT path FROM mail_attachment WHERE mail_id = " . $this->db->quote($mailId, 'integer')
        );

        while ($row = $this->db->fetchObject($query)) {
            return $row->path;
        }

        return '';
    }

    /**
    * get the path of a specific attachment
    * @param string filename
    * @param integer mail_id
    * @return string path
    */
    public function getAttachmentPath($a_filename, $a_mail_id)
    {
        $path = $this->getMailPath() . '/' . $this->getAttachmentPathByMailId($a_mail_id) . '/' . $a_filename;

        if (file_exists($path) && is_readable($path)) {
            return $path;
        }

        return '';
    }
    /**
    * adopt attachments (in case of forwarding a mail)
    * @param array attachments
    * @param integer mail_id
    * @access	public
    * @return string error message
    */
    public function adoptAttachments($a_attachments, $a_mail_id)
    {
        if (is_array($a_attachments)) {
            foreach ($a_attachments as $file) {
                $path = $this->getAttachmentPath($file, $a_mail_id);
                if (!copy($path, $this->getMailPath() . '/' . $this->user_id . '_' . $file)) {
                    return "ERROR: $this->getMailPath().'/'.$this->user_id.'_'.$file cannot be created";
                }
            }
        } else {
            return "ARRAY REQUIRED";
        }
        return '';
    }

    /**
    * check if directory is writable
    * overwritten method from base class
    * @access	private
    * @return bool
    */
    public function checkReadWrite()
    {
        if (is_writable($this->mail_path) && is_readable($this->mail_path)) {
            return true;
        } else {
            $this->ilias->raiseError("Mail directory is not readable/writable by webserver: " . $this->mail_path, $this->ilias->error_obj->FATAL);
        }
    }
    /**
    * get all attachments of a specific user
    * @access	public
    * @return array
    */
    public function getUserFilesData()
    {
        return $this->getUnsentFiles();
    }

    /**
    * get all files which are not sent
    * find them in directory data/mail/
    * @access	private
    * @return array
    */
    private function getUnsentFiles()
    {
        $files = array();

        $iter = new DirectoryIterator($this->mail_path);
        foreach ($iter as $file) {
            /**
             * @var $file SplFileInfo
             */
            if ($file->isFile()) {
                list($uid, $rest) = explode('_', $file->getFilename(), 2);
                if ($uid == $this->user_id) {
                    $files[] = array(
                        'name'     => $rest,
                        'size'     => $file->getSize(),
                        'ctime'    => $file->getCTime()
                    );
                }
            }
        }

        return $files;
    }
    
    /**
     * Store content as attachment
     * @param object $a_filename
     * @param object $a_content
     * @return
     */
    public function storeAsAttachment($a_filename, $a_content)
    {
        if (strlen($a_content) >= $this->getUploadLimit()) {
            return 1;
        }

        $name = ilUtil::_sanitizeFilemame($a_filename);
        $this->rotateFiles($this->getMailPath() . '/' . $this->user_id . '_' . $name);

        $abs_path = $this->getMailPath() . '/' . $this->user_id . '_' . $name;
        
        if (!$fp = @fopen($abs_path, 'w+')) {
            return false;
        }
        if (@fwrite($fp, $a_content) === false) {
            @fclose($fp);
            return false;
        }
        @fclose($fp);
        return true;
    }
    
    /**
     * @param array $file
     */
    public function storeUploadedFile($file)
    {
        $file['name'] = ilUtil::_sanitizeFilemame($file['name']);

        $this->rotateFiles($this->getMailPath() . '/' . $this->user_id . '_' . $file['name']);

        ilUtil::moveUploadedFile(
            $file['tmp_name'],
            $file['name'],
            $this->getMailPath() . '/' . $this->user_id . '_' . $file['name']
        );
    }

    /**
    * Copy files in mail directory. This is used for sending ILIAS generated mails with attachments
    * @param array Array with files. Absolute path required
    * @access	public
    * @return
    */
    public function copyAttachmentFile($a_abs_path, $a_new_name)
    {
        @copy($a_abs_path, $this->getMailPath() . "/" . $this->user_id . "_" . $a_new_name);
        
        return true;
    }
        


    /**
    * rotate files with same name
    * recursive method
    * @param string filename
    * @access	private
    * @return bool
    */
    public function rotateFiles($a_path)
    {
        if (file_exists($a_path)) {
            $this->rotateFiles($a_path . ".old");
            return \ilFileUtils::rename($a_path, $a_path . '.old');
        }
        return true;
    }
    /**
    * unlink files: expects an array of filenames e.g. array('foo','bar')
    * @param array filenames to delete
    * @access	public
    * @return string error message with filename that couldn't be deleted
    */
    public function unlinkFiles($a_filenames)
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
    /**
    * unlink one uploaded file expects a filename e.g 'foo'
    * @param string filename to delete
    * @access	public
    * @return bool
    */
    public function unlinkFile($a_filename)
    {
        if (file_exists($this->mail_path . '/' . basename($this->user_id . '_' . $a_filename))) {
            return unlink($this->mail_path . '/' . basename($this->user_id . '_' . $a_filename));
        }
    }

    /**
     * Resolves a path for a passed filename in regards of a user's mail attachment pool, meaning attachments not being sent
     * @param string $fileName
     * @return string
     */
    public function getAbsoluteAttachmentPoolPathByFilename(string $fileName) : string
    {
        return $this->getAbsoluteAttachmentPoolPathPrefix() . $fileName;
    }

    /**
     * Saves all attachment files in a specific mail directory .../mail/<calculated_path>/mail_<mail_id>_<user_id>/...
     * @param integer $a_mail_id id of mail in sent box
     * @param array $a_attachments to save
     */
    public function saveFiles($a_mail_id, array $a_attachments)
    {
        if (!is_numeric($a_mail_id) || $a_mail_id < 1) {
            throw new InvalidArgumentException('The passed mail_id must be a valid integer!');
        }

        foreach ($a_attachments as $attachment) {
            $this->saveFile($a_mail_id, $attachment);
        }
    }

    /**
     * @param $a_mail_id
     * @param $a_usr_id
     * @return \ilFSStorageMail
     */
    public static function getStorage($a_mail_id, $a_usr_id) : \ilFSStorageMail
    {
        static $fsstorage_cache = array();
        
        if (!is_object($fsstorage_cache[$a_mail_id][$a_usr_id])) {
            include_once 'Services/Mail/classes/class.ilFSStorageMail.php';
            $fsstorage_cache[$a_mail_id][$a_usr_id] = new ilFSStorageMail($a_mail_id, $a_usr_id);
        }
        
        return $fsstorage_cache[$a_mail_id][$a_usr_id];
    }
    
    /**
    * save attachment file in a specific mail directory .../mail/<calculated_path>/mail_<mail_id>_<user_id>/...
    * @param integer mail id of mail in sent box
    * @param array filenames to save
    * @access	public
    * @return bool
    */
    public function saveFile($a_mail_id, $a_attachment)
    {
        $oStorage = self::getStorage($a_mail_id, $this->user_id);
        $oStorage->create();
        $storage_directory = $oStorage->getAbsolutePath();
                
        if (@!is_dir($storage_directory)) {
            return false;
        }
        
        return copy(
            $this->mail_path . '/' . $this->user_id . '_' . $a_attachment,
            $storage_directory . '/' . $a_attachment
        );
    }
    /**
    * check if files exist
    * @param array filenames to check
    * @access	public
    * @return bool
    */
    public function checkFilesExist($a_files)
    {
        if ($a_files) {
            foreach ($a_files as $file) {
                if (!file_exists($this->mail_path . '/' . $this->user_id . '_' . $file)) {
                    return false;
                }
            }
            return true;
        }
        return true;
    }
    /**
    * assign attachments to mail directory
    * @param integer mail_id
    * @param integer key for directory assignment
    * @access	public
    * @return bool
    */
    public function assignAttachmentsToDirectory($a_mail_id, $a_sent_mail_id)
    {
        global $ilDB;
        
        /*		$query = "INSERT INTO mail_attachment ".
                    "SET mail_id = ".$ilDB->quote($a_mail_id).", ".
                    "path = ".$ilDB->quote($this->user_id."_".$a_sent_mail_id)." ";
                $res = $this->ilias->db->query($query);
        */

        $oStorage = self::getStorage($a_sent_mail_id, $this->user_id);
        $res = $ilDB->manipulateF(
            '
			INSERT INTO mail_attachment 
			( mail_id, path) VALUES (%s, %s)',
            array('integer', 'text'),
            array($a_mail_id, $oStorage->getRelativePathExMailDirectory())
        );
    }
    /**
    * dassign attachments from mail directory
    * @param integer mail_id
    * @access	public
    * @return bool
    */
    public function deassignAttachmentFromDirectory($a_mail_id)
    {
        global $ilDB;
        // IF IT'S THE LAST MAIL CONTAINING THESE ATTACHMENTS => DELETE ATTACHMENTS
        $res = $ilDB->query("SELECT path FROM mail_attachment
				WHERE mail_id = " . $ilDB->quote($a_mail_id, 'integer'));
    
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $path = $row->path;
        }
        if ($path) {
            $res = $ilDB->query("SELECT COUNT(mail_id) count_mail_id FROM mail_attachment 
					WHERE path = " . $ilDB->quote($path, 'text')) ;
            
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $cnt_mail_id = $row->count_mail_id;
            }
            if ($cnt_mail_id == 1) {
                $this->__deleteAttachmentDirectory($path);
            }
        }

        $res = $ilDB->manipulateF(
            "DELETE FROM mail_attachment 
				WHERE mail_id = %s",
            array('integer'),
            array($a_mail_id)
        );
        return true;
    }

    public function __deleteAttachmentDirectory($a_rel_path)
    {
        ilUtil::delDir($this->mail_path . "/" . $a_rel_path);
        
        return true;
    }

    /**
     *
     */
    protected function initAttachmentMaxUploadSize()
    {
        /** @todo mjansen: Unfortunately we cannot reuse the implementation of ilFileInputGUI */

        // Copy of ilFileInputGUI: begin
        // get the value for the maximal uploadable filesize from the php.ini (if available)
        $umf = ini_get("upload_max_filesize");
        // get the value for the maximal post data from the php.ini (if available)
        $pms = ini_get("post_max_size");

        //convert from short-string representation to "real" bytes
        $multiplier_a = array("K" => 1024, "M" => 1024 * 1024, "G" => 1024 * 1024 * 1024);

        $umf_parts = preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $pms_parts = preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if (count($umf_parts) == 2) {
            $umf = $umf_parts[0] * $multiplier_a[$umf_parts[1]];
        }
        if (count($pms_parts) == 2) {
            $pms = $pms_parts[0] * $multiplier_a[$pms_parts[1]];
        }

        // use the smaller one as limit
        $max_filesize = min($umf, $pms);

        if (!$max_filesize) {
            $max_filesize = max($umf, $pms);
        }
        // Copy of ilFileInputGUI: end

        $this->mail_max_upload_file_size = $max_filesize;
    }

    /**
     * Returns the number of bytes used on the harddisk for mail attachments,
     * by the user with the specified user id.
     * @param int user id.
     * @return array{'count'=>integer,'size'=>integer}
     *                            // an associative array with the disk
     *                            // usage in bytes and the count of attachments.
     */
    public static function _lookupDiskUsageOfUser($user_id)
    {
        // XXX - This method is extremely slow. We should
        // use a cache to speed it up, for example, we should
        // store the disk space used in table mail_attachment.
        global $DIC;

        $mail_data_dir = ilUtil::getDataDir('filesystem') . DIRECTORY_SEPARATOR . "mail";

        $q = "SELECT path " .
            "FROM mail_attachment ma " .
            "JOIN mail m ON ma.mail_id=m.mail_id " .
            "WHERE m.user_id = " . $DIC->database()->quote($user_id);
        $result_set = $DIC->database()->query($q);
        $size = 0;
        $count = 0;
        while ($row = $result_set->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $attachment_path = $mail_data_dir . DIRECTORY_SEPARATOR . $row['path'];
            $attachment_size = ilUtil::dirsize($attachment_path);
            if ($attachment_size != -1) {
                $size += $attachment_size;
            }
            $count++;
        }
        return array('count'=>$count, 'size'=>$size);
    }

    /**
     * Called when an ILIAS user account should be completely deleted
     */
    public function onUserDelete()
    {
        /**
         * @var $ilDB ilDBInterface
         */
        global $ilDB;
        
        // Delete uploaded mail files which are not attached to any message
        try {
            $iter = new RegexIterator(
                new DirectoryIterator($this->getMailPath()),
                '/^' . $this->user_id . '_/'
            );
            foreach ($iter as $file) {
                /**
                 * @var $file SplFileInfo
                 */

                if ($file->isFile()) {
                    @unlink($file->getPathname());
                }
            }
        } catch (Exception $e) {
        }

        // Select all files attached to messages which are not shared (... = 1) with other messages anymore
        $query = '
			SELECT DISTINCT(ma1.path)
			FROM mail_attachment ma1
			INNER JOIN mail
				ON mail.mail_id = ma1.mail_id
			WHERE mail.user_id = %s
			AND (SELECT COUNT(tmp.path) FROM mail_attachment tmp WHERE tmp.path = ma1.path) = 1
		';
        $res = $ilDB->queryF(
            $query,
            array('integer'),
            array($this->user_id)
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            try {
                $path = $this->getMailPath() . DIRECTORY_SEPARATOR . $row['path'];
                $iter = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($iter as $file) {
                    /**
                     * @var $file SplFileInfo
                     */

                    if ($file->isDir()) {
                        @rmdir($file->getPathname());
                    } else {
                        @unlink($file->getPathname());
                    }
                }
                @rmdir($path);
            } catch (Exception $e) {
            }
        }

        // Delete each mail attachment rows assigned to a message of the deleted user.
        $ilDB->manipulateF(
            '
				DELETE
				FROM mail_attachment
				WHERE EXISTS(
					SELECT mail.mail_id
					FROM mail
					WHERE mail.user_id = %s AND mail.mail_id = mail_attachment.mail_id
				)
				',
            array('integer'),
            array($this->user_id)
        );
    }

    /**
     * @param string $basename
     * @param int $mailId
     * @param array $files
     * @param bool $isDraft
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     * @throws ilException
     * @throws ilFileUtilsException
     */
    public function deliverAttachmentsAsZip(string $basename, int $mailId, $files = [], $isDraft = false)
    {
        $path = '';
        if (!$isDraft) {
            $path = $this->getAttachmentPathByMailId($mailId);
            if (0 === strlen($path)) {
                throw new \ilException('mail_download_zip_no_attachments');
            }
        }

        $downloadFilename = \ilUtil::getASCIIFilename($basename);
        if (0 === strlen($downloadFilename)) {
            $downloadFilename = 'attachments';
        }

        $processingDirectory = \ilUtil::ilTempnam();
        $relativeProcessingDirectory = basename($processingDirectory);

        $absoluteZipDirectory = $processingDirectory . '/' . $downloadFilename;
        $relativeZipDirectory = $relativeProcessingDirectory . '/' . $downloadFilename;

        $this->tmpDirectory->createDir($relativeZipDirectory);

        foreach ($files as $fileName) {
            if ($isDraft) {
                $source = str_replace(
                    $this->mail_path,
                    MAILPATH,
                    $this->getAbsoluteAttachmentPoolPathByFilename($fileName)
                );
            } else {
                $source = MAILPATH . '/' . $path . '/' . $fileName;
            }

            $source = str_replace('//', '/', $source);
            if (!$this->storageDirectory->has($source)) {
                continue;
            }

            $target = $relativeZipDirectory . '/' . $fileName;

            $stream = $this->storageDirectory->readStream($source);
            $this->tmpDirectory->writeStream($target, $stream);
        }

        $pathToZipFile = $processingDirectory . '/' . $downloadFilename . '.zip';
        \ilUtil::zip($absoluteZipDirectory, $pathToZipFile);

        $this->tmpDirectory->deleteDir($relativeZipDirectory);

        $delivery = new \ilFileDelivery($processingDirectory . '/' . $downloadFilename . '.zip');
        $delivery->setDisposition(\ilFileDelivery::DISP_ATTACHMENT);
        $delivery->setMimeType(\ilMimeTypeUtil::APPLICATION__ZIP);
        $delivery->setConvertFileNameToAsci(true);
        $delivery->setDownloadFileName(\ilFileUtils::getValidFilename($downloadFilename . '.zip'));
        $delivery->setDeleteFile(true);

        $delivery->deliver();
    }
}
