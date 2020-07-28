<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\File\Sanitation\FilePathSanitizer;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\FileUpload\Location;

require_once("Services/Object/classes/class.ilObject2.php");
require_once('Modules/File/classes/class.ilFSStorageFile.php');

/**
 * Class ilObjFile
 *
 * @author  Sascha Hofmann <shofmann@databay.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 *
 * @ingroup ModulesFile
 */
class ilObjFile extends ilObject2
{
    const MODE_FILELIST = "filelist";
    const MODE_OBJECT = "object";
    /**
     * @var bool
     */
    protected $no_meta_data_creation;
    /**
     * @var string
     */
    protected $filename = '';
    /**
     * @var string
     */
    protected $filetype = '';
    /**
     * @var string
     */
    protected $filemaxsize = "20000000";    // not used yet
    /**
     * @var string
     */
    protected $filesize;
    /**
     * @var bool
     */
    public $raise_upload_error;
    /**
     * @var string
     */
    public $mode = self::MODE_OBJECT;
    /**
     * @var int
     */
    protected $page_count = 0;
    /**
     * @var bool
     */
    protected $rating = false;
    /**
     * @var \ilFSStorageFile
     */
    private $file_storage = null;
    /**
     * @var \ilLogger
     */
    protected $log = null;
    /**
     * @var int
     */
    protected $version = 1;
    /**
     * @var int
     */
    protected $max_version = 1;
    /**
     * @var string
     */
    protected $action = null;
    /**
     * @var int
     */
    protected $rollback_version = null;
    /**
     * @var int
     */
    protected $rollback_user_id = null;


    /**
     * ilObjFile constructor.
     *
     * @param int  $a_id                ID of the object, ref_id or obj_id possible
     * @param bool $a_call_by_reference defines the $a_id a ref_id
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->version = 0;
        $this->max_version = 0;
        $this->raise_upload_error = true;

        $this->log = ilLoggerFactory::getLogger('file');

        parent::__construct($a_id, $a_call_by_reference);

        if ($this->getId()) {
            $this->initFileStorage();
        }
    }


    public function initType()
    {
        $this->type = "file";
    }


    /**
     * create object
     *
     * @param bool upload mode (if enabled no entries in file_data will be done)
     */
    protected function doCreate($a_upload = false)
    {
        $this->createProperties($a_upload);
    }


    /**
     * The basic properties of a file object are stored in table object_data.
     * This is not sufficient for a file object. Therefore we create additional
     * properties in table file_data.
     * This method has been put into a separate operation, to allow a WebDAV Null resource
     * (class.ilObjNull.php) to become a file object.
     */
    public function createProperties($a_upload = false)
    {
        global $DIC;

        // Create file directory
        $this->initFileStorage();
        $this->file_storage->create();

        if ($a_upload) {
            return true;
        }

        // not upload mode
        ilHistory::_createEntry($this->getId(), "create", $this->getFileName() . ",1" . ",1");
        $this->addNewsNotification("file_created");

        // New Item
        $default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($_GET['ref_id']);
        if ($default_visibility == "public") {
            ilBlockSetting::_write("news", "public_notifications", 1, 0, $this->getId());
        }

        // log creation
        $this->log->debug("ilObjFile::createProperties, ID: " . $this->getId() . ", Name: "
            . $this->getFileName() . ", Type: " . $this->getFileType() . ", Size: "
            . $this->getFileSize() . ", Mode: " . $this->getMode() . ", Name(Bytes): "
            . implode(":", ilStr::getBytesForString($this->getFileName())));
        $this->log->logStack(ilLogLevel::DEBUG);

        $DIC->database()->insert('file_data', $this->getArrayForDatabase());

        //add metadata to database
        $metadata = [
            'meta_lifecycle_id' => ['integer', $DIC->database()->nextId('il_meta_lifecycle')],
            'rbac_id' => ['integer', $this->getId()],
            'obj_id' => ['integer', $this->getId()],
            'obj_type' => ['text', "file"],
            'meta_version' => ['integer', (int) $this->getVersion()],
        ];
        $DIC->database()->insert('il_meta_lifecycle', $metadata);

        // no meta data handling for file list files
        if ($this->getMode() != self::MODE_FILELIST) {
            $this->createMetaData();
        }
    }


    /**
     * @param bool $a_status
     */
    public function setNoMetaDataCreation($a_status)
    {
        $this->no_meta_data_creation = (bool) $a_status;
    }


    protected function beforeCreateMetaData()
    {
        return !(bool) $this->no_meta_data_creation;
    }


    protected function beforeUpdateMetaData()
    {
        return !(bool) $this->no_meta_data_creation;
    }


    /**
     * create file object meta data
     */
    protected function doCreateMetaData()
    {
        // add technical section with file size and format
        $md_obj = new ilMD($this->getId(), 0, $this->getType());
        $technical = $md_obj->addTechnical();
        $technical->setSize($this->getFileSize());
        $technical->save();
        $format = $technical->addFormat();
        $format->setFormat($this->getFileType());
        $format->save();
        $technical->update();
    }


    protected function beforeMDUpdateListener($a_element)
    {
        // Check file extension
        // Removing the file extension is not allowed
        include_once 'Services/MetaData/classes/class.ilMD.php';
        $md = new ilMD($this->getId(), 0, $this->getType());
        if (!is_object($md_gen = $md->getGeneral())) {
            return false;
        }
        $title = $this->checkFileExtension($this->getFileName(), $md_gen->getTitle());
        $md_gen->setTitle($title);
        $md_gen->update();

        return true;
    }


    protected function doMDUpdateListener($a_element)
    {
        // handling for technical section
        include_once 'Services/MetaData/classes/class.ilMD.php';

        switch ($a_element) {
            case 'Technical':

                // Update Format (size is not stored in db)
                $md = new ilMD($this->getId(), 0, $this->getType());
                if (!is_object($md_technical = $md->getTechnical())) {
                    return false;
                }

                foreach ($md_technical->getFormatIds() as $id) {
                    $md_format = $md_technical->getFormat($id);
                    ilObjFile::_writeFileType($this->getId(), $md_format->getFormat());
                    $this->setFileType($md_format->getFormat());
                    break;
                }

                break;
        }

        return true;
    }


    /**
     * @param int $a_version
     *
     * @return string
     */
    public function getDirectory($a_version = 0)
    {
        $version_subdir = "";

        if ($a_version) {
            // BEGIN WebDAV Avoid double slash before version subdirectory
            $version_subdir = sprintf("%03d", $a_version);
            // END WebDAV Avoid  double slash before version subdirectory
        }

        if (!is_object($this->file_storage)) {
            $this->initFileStorage();
        }

        $str = $this->file_storage->getAbsolutePath() . '/' . $version_subdir;

        return $str;
    }


    public function createDirectory()
    {
        ilUtil::makeDirParents($this->getDirectory());
    }


    public function raiseUploadError($a_raise = true)
    {
        $this->raise_upload_error = $a_raise;
    }


    /**
     * @param      $a_upload_file
     * @param      $a_filename
     * @param bool $a_prevent_preview
     *
     * @return \ILIAS\FileUpload\DTO\UploadResult
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function getUploadFile($a_upload_file, $a_filename, $a_prevent_preview = false)
    {
        global $DIC;

        $upload = $DIC->upload();
        $result = null;

        if ($upload->hasUploads()) {
            if (!$upload->hasBeenProcessed()) {
                $upload->process();
            }
            /**
             * @var $result \ILIAS\FileUpload\DTO\UploadResult
             */
            $result = $upload->getResults()[$a_upload_file];
            if ($result->getStatus()->getCode() === \ILIAS\FileUpload\DTO\ProcessingStatus::OK) {
                $metadata = $result->getMetaData();
                if ($metadata->has(ilCountPDFPagesPreProcessors::PAGE_COUNT)) {
                    $this->setPageCount($metadata->get(ilCountPDFPagesPreProcessors::PAGE_COUNT));
                    $this->doUpdate();
                }
                $a_name = $result->getName();
                $this->setFileName($a_name);

                // bugfix mantis 26236:
                // ensure that version and max_version are correct to prevent the upload file from being versioned incorrectly
                if ($this->getVersion() > 0) {
                    $file_hist_entries = (array) ilHistory::_getEntriesForObject($this->getId(), $this->getType());
                    $highest_version = 0;
                    foreach ($file_hist_entries as $file_hist_entry) {
                        $version = $this->parseInfoParams($file_hist_entry)['version'];
                        if ($version > $highest_version) {
                            $highest_version = $version;
                        }
                    }
                    if ($this->getVersion() < $highest_version) {
                        $this->setVersion($highest_version);
                    }
                    if ($this->getVersion() > $this->getMaxVersion()) {
                        $this->setMaxVersion($this->getVersion());
                    }
                }

                $this->setVersion($this->getMaxVersion() + 1);
                $this->setMaxVersion($this->getMaxVersion() + 1);

                if (!is_dir($this->getDirectory($this->getVersion()))) {
                    ilUtil::makeDirParents($this->getDirectory($this->getVersion()));
                }

                $target_directory = $this->getDirectory($this->getVersion()) . "/";
                $relative_path_to_file = LegacyPathHelper::createRelativePath($target_directory);

                $upload->moveOneFileTo($result, $relative_path_to_file, Location::STORAGE);

                $this->handleQuotaUpdate($this);

                // create preview?
                if (!$a_prevent_preview) {
                    $this->createPreview(false);
                }
            } else {
                throw new ilFileException('not supported File');
            }
        }

        return $result;
    }


    /**
     * @param $a_upload_file
     * @param $a_filename
     *
     * @return \ILIAS\FileUpload\DTO\UploadResult
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function replaceFile($a_upload_file, $a_filename)
    {
        if ($result = $this->getUploadFile($a_upload_file, $a_filename, true)) {
            ilHistory::_createEntry($this->getId(), "replace", $a_filename . "," . $this->getVersion() . "," . $this->getMaxVersion());
            $this->addNewsNotification("file_updated");

            // create preview
            $this->createPreview(true);
        }

        return $result;
    }


    /**
     * @param $a_upload_file
     * @param $a_filename
     *
     * @return \ILIAS\FileUpload\DTO\UploadResult
     * @throws \ILIAS\FileUpload\Collection\Exception\NoSuchElementException
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function addFileVersion($a_upload_file, $a_filename)
    {
        if ($result = $this->getUploadFile($a_upload_file, $a_filename, true)) {
            ilHistory::_createEntry($this->getId(), "new_version", $result->getName() . "," . $this->getVersion() . "," . $this->getMaxVersion());
            $this->addNewsNotification("file_updated");

            // create preview
            $this->createPreview($this->getVersion() > 1);
        }

        return $result;
    }


    /**
     * copy file
     */
    public function copy($a_source, $a_destination)
    {
        return copy($a_source, $this->getDirectory() . "/" . $a_destination);
    }


    /**
     * clear data directory
     */
    public function clearDataDirectory()
    {
        ilUtil::delDir($this->getDirectory());
        $this->createDirectory();
    }


    /**
     * Deletes the specified history entries or all entries if no ids are specified.
     *
     * @param array $a_hist_entry_ids The ids of the entries to delete or null to delete all entries
     */
    public function deleteVersions($a_hist_entry_ids = null)
    {
        if ($a_hist_entry_ids == null || count($a_hist_entry_ids) < 1) {
            $this->clearDataDirectory();

            ilHistory::_removeEntriesForObject($this->getId());

            self::handleQuotaUpdate($this);
        } else {
            $actualVersionDeleted = false;

            // get all versions
            $versions = $this->getVersions();

            // delete each version
            foreach ($a_hist_entry_ids as $hist_id) {
                $entry = null;

                // get version
                foreach ($versions as $index => $version) {
                    if ($version["hist_entry_id"] == $hist_id) {
                        // remove each history entry
                        ilHistory::_removeEntryByHistoryID($hist_id);

                        // delete directory
                        $version_dir = $this->getDirectory($version["version"]);
                        ilUtil::delDir($version_dir);

                        // is actual version?
                        if ($version["version"] == $this->getVersion()) {
                            $actualVersionDeleted = true;
                        }

                        // remove from array
                        unset($versions[$index]);
                        break;
                    }
                }
            }

            // update actual version if it was deleted before
            if ($actualVersionDeleted) {
                // get newest version (already sorted by getVersions)
                $version = reset($versions);
                $version['max_version'] = $this->getMaxVersion();
                $this->updateWithVersion($version);
            } else {
                // updateWithVersion() will trigger quota, too
                self::handleQuotaUpdate($this);
            }
        }
    }


    protected function doRead()
    {
        global $DIC;

        $q = "SELECT * FROM file_data WHERE file_id = %s";
        $r = $DIC->database()->queryF($q, ['integer'], [$this->getId()]);
        $row = $r->fetchObject();

        $this->setFileName($row->file_name);
        $this->setFileType($row->file_type);
        $this->setFileSize($row->file_size);
        $this->setVersion($row->version ? $row->version : 1);
        $this->setMaxVersion($row->max_version ? $row->max_version : 1);
        $this->setMode($row->f_mode);
        $this->setRating($row->rating);
        $this->setPageCount($row->page_count);

        $this->initFileStorage();
    }


    protected function beforeUpdate()
    {
        // no meta data handling for file list files
        if ($this->getMode() != self::MODE_FILELIST) {
            $this->updateMetaData();
        }

        return true;
    }


    protected function doUpdate()
    {
        global $DIC;

        $a_columns = $this->getArrayForDatabase();
        $DIC->database()->update('file_data', $a_columns, [
            'file_id' => [
                'integer',
                $this->getId(),
            ],
        ]);

        // update metadata with the current file version
        $meta_version_column = ['meta_version' => ['integer', (int) $this->getVersion()]];
        $DIC->database()->update('il_meta_lifecycle', $meta_version_column, [
            'obj_id' => [
                'integer',
                $this->getId(),
            ],
        ]);

        self::handleQuotaUpdate($this);

        return true;
    }


    /**
     * update meta data
     */
    protected function doUpdateMetaData()
    {
        // add technical section with file size and format
        $md_obj = new ilMD($this->getId(), 0, $this->getType());
        if (!is_object($technical = $md_obj->getTechnical())) {
            $technical = $md_obj->addTechnical();
            $technical->save();
        }
        $technical->setSize($this->getFileSize());

        $format_ids = $technical->getFormatIds();
        if (count($format_ids) > 0) {
            $format = $technical->getFormat($format_ids[0]);
            $format->setFormat($this->getFileType());
            $format->update();
        } else {
            $format = $technical->addFormat();
            $format->setFormat($this->getFileType());
            $format->save();
        }
        $technical->update();
    }


    /**
     * @param string $a_name
     */
    public function setFileName($a_name)
    {
        $this->filename = $a_name;
    }


    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }


    /**
     * @param string $a_type
     */
    public function setFileType($a_type)
    {
        $this->filetype = $a_type;
    }


    /**
     * @return string
     */
    public function getFileType()
    {
        return $this->filetype;
    }


    /**
     * @param $a_size
     */
    public function setFileSize($a_size)
    {
        $this->filesize = $a_size;
    }


    public function getFileSize()
    {
        return $this->filesize;
    }


    public function setAction($a_action)
    {
        $this->action = $a_action;
    }


    public function getAction()
    {
        return $this->action;
    }


    public function setRollbackVersion($a_rollback_version)
    {
        $this->rollback_version = $a_rollback_version;
    }


    public function getRollbackVersion()
    {
        return $this->rollback_version;
    }


    public function setRollbackUserId($a_rollback_user_id)
    {
        $this->rollback_user_id = $a_rollback_user_id;
    }


    public function getRollbackUserId()
    {
        return $this->rollback_user_id;
    }


    /**
     * Gets the disk usage of the object in bytes.
     *
     * @access    public
     * @return    integer        the disk usage in bytes
     */
    public function getDiskUsage()
    {
        require_once("./Modules/File/classes/class.ilObjFileAccess.php");

        return ilObjFileAccess::_lookupDiskUsage($this->id);
    }


    // END PATCH WebDAV Encapsulate file access in ilObjFile class.
    public function getFile($a_hist_entry_id = null)
    {
        if (is_null($a_hist_entry_id)) {
            $file = $this->getDirectory($this->getVersion()) . "/" . $this->getFileName();
        } else {
            require_once("./Services/History/classes/class.ilHistory.php");
            $entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);

            if ($entry === false) {
                return false;
            }

            $data = $this->parseInfoParams($entry);
            $file = $this->getDirectory($data["version"]) . "/" . $data["filename"];
        }

        return $file;
    }


    // END PATCH WebDAV Encapsulate file access in ilObjFile class.

    public function setVersion($a_version)
    {
        $this->version = $a_version;
    }


    public function getVersion()
    {
        return $this->version;
    }


    public function setMaxVersion($a_max_version)
    {
        $this->max_version = $a_max_version;
    }


    public function getMaxVersion()
    {
        return $this->max_version;
    }


    /**
     * mode is object or filelist
     *
     * @param string $a_mode mode
     */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }


    /**
     * mode is object or filelist
     *
     * @return    string        mode
     */
    public function getMode()
    {
        return $this->mode;
    }


    public static function _writeFileType($a_id, $a_format)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "UPDATE file_data SET " . " file_type = " . $ilDB->quote($a_format, 'text')
            . " WHERE file_id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->manipulate($q);
    }


    /**
     * @param $a_id
     *
     * @return string
     * @deprecated Static methods will be removed in a future version of ILIAS
     */
    public static function _lookupFileName($a_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $q = "SELECT * FROM file_data WHERE file_id = " . $ilDB->quote($a_id, 'integer');
        $r = $ilDB->query($q);
        $row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT);

        $strip_slashes = ilUtil::stripSlashes($row->file_name);

        return $strip_slashes;
    }


    /** Lookups the file size of the file in bytes. */
    public static function _lookupFileSize($a_id)
    {
        require_once("./Modules/File/classes/class.ilObjFileAccess.php");

        return ilObjFileAccess::_lookupFileSize($a_id);
    }


    /**
     * lookup version
     */
    public static function _lookupVersion($a_id)
    {
        require_once("./Modules/File/classes/class.ilObjFileAccess.php");

        return ilObjFileAccess::_lookupVersion($a_id);
    }


    /**
     * Determine File Size
     */
    public function determineFileSize($a_hist_entry_id = null)
    {
        if (is_null($a_hist_entry_id)) {
            $file = $this->getDirectory($this->getVersion()) . "/" . $this->getFileName();
        } else {
            require_once("./Services/History/classes/class.ilHistory.php");
            $entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);

            if ($entry === false) {
                return false;
            }

            $data = $this->parseInfoParams($entry);
            $file = $this->getDirectory($data["version"]) . "/" . $data["filename"];
        }
        if (is_file($file)) {
            $this->setFileSize(filesize($file));
        }
    }


    /**
     * @param null $a_hist_entry_id
     *
     * @return bool
     */
    public function sendFile($a_hist_entry_id = null)
    {
        $s = new FilePathSanitizer($this);
        $s->sanitizeIfNeeded();

        if (is_null($a_hist_entry_id)) {
            $file = $this->getDirectory($this->getVersion()) . "/" . $this->getFileName();
            $file = ilFileUtils::getValidFilename($file);
        } else {
            $entry = ilHistory::_getEntryByHistoryID($a_hist_entry_id);
            $data = $this->parseInfoParams($entry);
            $file = $this->getDirectory($data["version"]) . "/" . $data["filename"];
        }


        if ($this->file_storage->fileExists($file)) {
            global $DIC;
            $ilClientIniFile = $DIC['ilClientIniFile'];
            /**
             * @var $ilClientIniFile ilIniFile
             */

            $ilFileDelivery = new ilFileDelivery($file);
            $ilFileDelivery->setDisposition($this->isInline() ? ilFileDelivery::DISP_INLINE : ilFileDelivery::DISP_ATTACHMENT);
            $ilFileDelivery->setMimeType($this->guessFileType($file));
            $ilFileDelivery->setConvertFileNameToAsci((bool) !$ilClientIniFile->readVariable('file_access', 'disable_ascii'));

            // also returning the 'real' filename if a history file is delivered
            if ($ilClientIniFile->readVariable('file_access', 'download_with_uploaded_filename')
                != '1'
                && is_null($a_hist_entry_id)
            ) {
                $ilFileDelivery->setDownloadFileName(ilFileUtils::getValidFilename($this->getTitle()));
            } else {
                // $download_file_name = basename($file);
                /* FSX Info: basename has a Bug with Japanese and other characters, see:
                 * http://stackoverflow.com/questions/32115609/basename-fail-when-file-name-start-by-an-accent
                 * Therefore we can no longer use basename();
                 */
                $parts = explode(DIRECTORY_SEPARATOR, $file);
                $download_file_name = end($parts);
                $download_file_name = ilFileUtils::getValidFilename($download_file_name);
                $ilFileDelivery->setDownloadFileName($download_file_name);
            }
            $ilFileDelivery->deliver();

            return true;
        }

        throw new FileNotFoundException("This file cannot be found in ILIAS or has been blocked due to security reasons.");
    }


    /**
     * Returns the extension of the file name converted to lower-case.
     * e.g. returns 'pdf' for 'document.pdf'.
     */
    public function getFileExtension()
    {
        require_once 'Modules/File/classes/class.ilObjFileAccess.php';

        return ilObjFileAccess::_getFileExtension($this->getTitle());
    }


    /**
     * Returns true, if this file should be displayed inline in a browser
     * window. This is especially useful for PDF documents, HTML pages,
     * and for images which are directly supported by the browser.
     */
    public function isInline()
    {
        require_once 'Modules/File/classes/class.ilObjFileAccess.php';

        return ilObjFileAccess::_isFileInline($this->getTitle());
    }


    /**
     * Returns true, if this file should be hidden in the repository view.
     */
    public function isHidden()
    {
        require_once 'Modules/File/classes/class.ilObjFileAccess.php';

        return ilObjFileAccess::_isFileHidden($this->getTitle());
    }
    // END WebDAV: Get file extension, determine if file is inline, guess file type.


    /**
     * Guesses the file type based on the current values returned by getFileType()
     * and getFileExtension().
     * If getFileType() returns 'application/octet-stream', the file extension is
     * used to guess a more accurate file type.
     */
    public function guessFileType($a_file = "")
    {
        $path = pathinfo($a_file);
        if ($path["extension"] != "") {
            $filename = $path["basename"];
        } else {
            $filename = "dummy." . $this->getFileExtension();
        }
        include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
        $mime = ilMimeTypeUtil::getMimeType($a_file, $filename, $this->getFileType());

        return $mime;
    }


    /**
     * Clone
     *
     * @access public
     *
     * @param object clone
     * @param int    target id
     * @param int    copy id
     *
     */
    protected function doCloneObject($a_new_obj, $a_target_id, $a_copy_id = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $a_new_obj->createDirectory();
        $this->cloneMetaData($a_new_obj);

        // Copy all file versions
        ilUtil::rCopy($this->getDirectory(), $a_new_obj->getDirectory());

        // object created now copy other settings
        // bugfix mantis 26131
        $DIC->database()->insert('file_data', $this->getArrayForDatabase($a_new_obj->getId()));

        // copy all previews
        require_once("./Services/Preview/classes/class.ilPreview.php");
        ilPreview::copyPreviews($this->getId(), $a_new_obj->getId());

        // copy history entries
        require_once("./Services/History/classes/class.ilHistory.php");
        ilHistory::_copyEntriesForObject($this->getId(), $a_new_obj->getId());

        // Copy learning progress settings
        include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($a_new_obj->getId());
        unset($obj_settings);

        // add news notification
        $a_new_obj->addNewsNotification("file_created");

        return $a_new_obj;
    }


    protected function beforeDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // check, if file is used somewhere
        $usages = $this->getUsages();
        if (count($usages) == 0) {
            return true;
        }

        return false;
    }


    protected function doDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // delete file data entry
        $q = "DELETE FROM file_data WHERE file_id = " . $ilDB->quote($this->getId(), 'integer');
        $this->ilias->db->query($q);

        // delete history entries
        require_once("./Services/History/classes/class.ilHistory.php");
        ilHistory::_removeEntriesForObject($this->getId());

        self::handleQuotaUpdate($this);

        // delete entire directory and its content
        if (@is_dir($this->getDirectory())) {
            ilUtil::delDir($this->getDirectory());
        }

        // delete meta data
        if ($this->getMode() != self::MODE_FILELIST) {
            $this->deleteMetaData();
        }

        // delete preview
        $this->deletePreview();
    }


    /**
     * export files of object to target directory
     * note: target directory must be the export target directory,
     * "/objects/il_<inst>_file_<file_id>/..." will be appended to this directory
     *
     * @param string $a_target_dir target directory
     */
    public function export($a_target_dir)
    {
        $subdir = "il_" . IL_INST_ID . "_file_" . $this->getId();
        ilUtil::makeDir($a_target_dir . "/objects/" . $subdir);

        $filedir = $this->getDirectory($this->getVersion());

        if (@!is_dir($filedir)) {
            $filedir = $this->getDirectory();
        }

        ilUtil::rCopy($filedir, $a_target_dir . "/objects/" . $subdir);
    }


    /**
     * static delete all usages of
     */
    public static function _deleteAllUsages($a_type, $a_id, $a_usage_hist_nr = 0, $a_usage_lang = "-")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $and_hist = ($a_usage_hist_nr !== false) ? " AND usage_hist_nr = "
            . $ilDB->quote($a_usage_hist_nr, "integer") : "";

        $file_ids = array();
        $set = $ilDB->query("SELECT id FROM file_usage" . " WHERE usage_type = "
            . $ilDB->quote($a_type, "text") . " AND usage_id= "
            . $ilDB->quote($a_id, "integer") . " AND usage_lang= "
            . $ilDB->quote($a_usage_lang, "text") . $and_hist);
        while ($row = $ilDB->fetchAssoc($set)) {
            $file_ids[] = $row["id"];
        }

        $ilDB->manipulate("DELETE FROM file_usage WHERE usage_type = "
            . $ilDB->quote($a_type, "text") . " AND usage_id = "
            . $ilDB->quote((int) $a_id, "integer") . " AND usage_lang= "
            . $ilDB->quote($a_usage_lang, "text") . " AND usage_hist_nr = "
            . $ilDB->quote((int) $a_usage_hist_nr, "integer"));

        foreach ($file_ids as $file_id) {
            self::handleQuotaUpdate(new self($file_id, false));
        }
    }


    /**
     * save usage
     */
    public static function _saveUsage($a_file_id, $a_type, $a_id, $a_usage_hist_nr = 0, $a_usage_lang = "-")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // check if file really exists
        if (ilObject::_lookupType($a_file_id) != "file") {
            return;
        }
        // #15143
        $ilDB->replace("file_usage", array(
            "id" => array("integer", (int) $a_file_id),
            "usage_type" => array("text", (string) $a_type),
            "usage_id" => array("integer", (int) $a_id),
            "usage_hist_nr" => array("integer", (int) $a_usage_hist_nr),
            "usage_lang" => array("text", $a_usage_lang),
        ), array());

        self::handleQuotaUpdate(new self($a_file_id, false));
    }


    /**
     * get all usages of file object
     */
    public function getUsages()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // get usages in learning modules
        $q = "SELECT * FROM file_usage WHERE id = " . $ilDB->quote($this->getId(), "integer");
        $us_set = $ilDB->query($q);
        $ret = array();
        while ($us_rec = $ilDB->fetchAssoc($us_set)) {
            $ret[] = array(
                "type" => $us_rec["usage_type"],
                "id" => $us_rec["usage_id"],
                "lang" => $us_rec["usage_lang"],
                "hist_nr" => $us_rec["usage_hist_nr"],
            );
        }

        return $ret;
    }


    /**
     * get all files of an object
     *
     * @param string $a_type object type (e.g. "lm:pg")
     * @param int    $a_id   object id
     *
     * @return    array        array of file ids
     */
    public static function _getFilesOfObject($a_type, $a_id, $a_usage_hist_nr = 0, $a_usage_lang = "-")
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $lstr = "";
        if ($a_usage_lang != "") {
            $lstr = "usage_lang = " . $ilDB->quote((string) $a_usage_lang, "text") . " AND ";
        }

        // get usages in learning modules
        $q = "SELECT * FROM file_usage WHERE " . "usage_id = " . $ilDB->quote((int) $a_id, "integer")
            . " AND " . "usage_type = " . $ilDB->quote((string) $a_type, "text") . " AND " . $lstr
            . "usage_hist_nr = " . $ilDB->quote((int) $a_usage_hist_nr, "integer");
        $file_set = $ilDB->query($q);
        $ret = array();
        while ($file_rec = $ilDB->fetchAssoc($file_set)) {
            $ret[$file_rec["id"]] = $file_rec["id"];
        }

        return $ret;
    }


    // TODO: What is this function good for??
    public function getXMLZip()
    {
        global $DIC;
        $ilias = $DIC['ilias'];

        $zip = PATH_TO_ZIP;

        exec($zip . ' ' . ilUtil::escapeShellArg($this->getDirectory() . '/' . $this->getFileName())
            . " " . ilUtil::escapeShellArg($this->getDirectory() . '/' . '1.zip'));

        return $this->getDirectory() . '/1.zip';
    }


    public function addNewsNotification($a_lang_var)
    {
        // BEGIN WebDAV Suppress news notification for hidden files
        if ($this->isHidden()) {
            return;
        }
        // END WebDAV Suppress news notification for hidden files

        global $DIC;
        $ilUser = $DIC['ilUser'];

        // Add Notification to news
        include_once("./Services/News/classes/class.ilNewsItem.php");
        include_once("./Modules/File/classes/class.ilObjFileAccess.php");
        $news_item = new ilNewsItem();
        $news_item->setContext($this->getId(), $this->getType());
        $news_item->setPriority(NEWS_NOTICE);
        $news_item->setTitle($a_lang_var);
        $news_item->setContentIsLangVar(true);
        if ($this->getDescription() != "") {
            $news_item->setContent("<p>" . $this->getDescription() . "</p>");
        }
        $news_item->setUserId($ilUser->getId());
        $news_item->setVisibility(NEWS_USERS);
        $news_item->create();
    }


    /**
     * init file storage object
     *
     * @access public
     *
     */
    public function initFileStorage()
    {
        $this->file_storage = new ilFSStorageFile($this->getId());

        return true;
    }


    /**
     * storeUnzipedFile
     *
     * Stores Files unzipped from uploaded archive in filesystem
     *
     * @param string $a_upload_file
     * @param string $a_filename
     */

    public function storeUnzipedFile($a_upload_file, $a_filename)
    {
        $this->setVersion($this->getVersion() + 1);

        if (@!is_dir($this->getDirectory($this->getVersion()))) {
            ilUtil::makeDir($this->getDirectory($this->getVersion()));
        }

        $file = $this->getDirectory($this->getVersion()) . "/" . $a_filename;

        $file = ilFileUtils::getValidFilename($file);

        ilFileUtils::rename($a_upload_file, $file);

        // create preview
        $this->createPreview();
    }


    /**
     * @param      $obj_id
     * @param null $a_version
     *
     * @return bool|string
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     */
    public static function _lookupAbsolutePath($obj_id, $a_version = null)
    {
        $file_object = new self($obj_id, false);
        $s = new FilePathSanitizer($file_object);
        $s->sanitizeIfNeeded();

        return $file_object->getFile($a_version);
    }


    /**
     * Check if the file extension does still exist after an update of the title
     *
     * @return
     */
    public function checkFileExtension($new_filename, $new_title)
    {
        include_once './Modules/File/classes/class.ilObjFileAccess.php';
        $fileExtension = ilObjFileAccess::_getFileExtension($new_filename);
        $titleExtension = ilObjFileAccess::_getFileExtension($new_title);
        if ($titleExtension != $fileExtension && strlen($fileExtension) > 0) {
            // remove old extension
            $pi = pathinfo($this->getFileName());
            $suffix = $pi["extension"];
            if ($suffix != "") {
                if (substr($new_title, strlen($new_title) - strlen($suffix) - 1) == "." . $suffix) {
                    $new_title = substr($new_title, 0, strlen($new_title) - strlen($suffix) - 1);
                }
            }
            $new_title .= '.' . $fileExtension;
        }

        return $new_title;
    }


    /**
     * Gets the file versions for this object.
     *
     * @param array $version_ids The file versions to get. If not specified all versions are
     *                           returned.
     *
     * @return array The file versions.
     *
     *               Example:  array (
     * 'date' => '2019-07-25 11:19:51',
     * 'user_id' => '6',
     * 'obj_id' => '287',
     * 'obj_type' => 'file',
     * 'action' => 'create',
     * 'info_params' => 'chicken_outlined.pdf,1,1',
     * 'user_comment' => '',
     * 'hist_entry_id' => '3',
     * 'title' => NULL,
     * )
     */
    public function getVersions($version_ids = null) : array
    {
        $versions = (array) ilHistory::_getEntriesForObject($this->getId(), $this->getType());

        if ($version_ids != null && count($version_ids) > 0) {
            foreach ($versions as $index => $version) {
                if (!in_array($version["hist_entry_id"], $version_ids, true)) {
                    unset($versions[$index]);
                }
            }
        }

        // add custom entries
        foreach ($versions as $index => $version) {
            $params = $this->parseInfoParams($version);
            $versions[$index] = array_merge($version, $params);
        }

        // sort by version number (hist_entry_id will do for that)
        usort($versions, array($this, "compareVersions"));

        return $versions;
    }


    /**
     * Gets a specific file version.
     *
     * @param int $version_id The version id to get.
     *
     * @return array The specific version or false if the version was not found.
     */
    public function getSpecificVersion($version_id)
    {
        include_once("./Services/History/classes/class.ilHistory.php");
        $version = ilHistory::_getEntryByHistoryID($version_id);
        if ($version === false) {
            return false;
        }

        // ilHistory returns different keys in _getEntryByHistoryID and _getEntriesForObject
        // so this makes it the same
        $version["hist_entry_id"] = $version["id"];
        $version["user_id"] = $version["usr_id"];
        $version["date"] = $version["hdate"];
        unset($version["id"], $version["usr_id"], $version["hdate"]);

        // parse params
        $params = $this->parseInfoParams($version);

        return array_merge($version, $params);
    }


    /**
     * Makes the specified version the current one and returns theSummary of rollbackVersion
     *
     * @param int $version_id The id of the version to make the current one.
     *
     * @return array The new actual version.
     */
    public function rollback($version_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        $source = $this->getSpecificVersion($version_id);
        if ($source === false) {
            $this->ilErr->raiseError($this->lng->txt("obj_not_found"), $this->ilErr->MESSAGE);
        }

        // get the new version number
        $new_version_nr = $this->getMaxVersion() + 1;
        $this->setMaxVersion($new_version_nr);

        // copy file
        $source_path = $this->getDirectory($source["version"]) . "/" . $source["filename"];
        $dest_dir = $this->getDirectory($new_version_nr);
        if (@!is_dir($dest_dir)) {
            ilUtil::makeDir($dest_dir);
        }

        copy($source_path, $dest_dir . "/" . $source["filename"]);

        // create new history entry based on the old one
        include_once("./Services/History/classes/class.ilHistory.php");
        // bugfix mantis 26236: added rollback info to version instead of max_version to ensure compatibility with older ilias versions
        ilHistory::_createEntry($this->getId(), "rollback", $source["filename"] . ","
            . $new_version_nr . "|"
            . $source["version"] . "|"
            . $ilUser->getId() . ","
            . $this->getMaxVersion());

        // get id of newest entry
        $entries = ilHistory::_getEntriesForObject($this->getId());
        $newest_entry_id = 0;
        foreach ($entries as $entry) {
            if ($entry["action"] == "rollback") {
                $newest_entry_id = $entry["hist_entry_id"];
            }
        }
        $new_version = $this->getSpecificVersion($newest_entry_id);
        $new_version['version'] = $new_version_nr;
        $new_version['max_version'] = $new_version_nr;

        // change user back to the original uploader
        ilHistory::_changeUserId($new_version["hist_entry_id"], $source["user_id"]);

        // update this file with the new version
        $this->updateWithVersion($new_version);

        $this->addNewsNotification("file_updated");

        return $new_version;
    }


    /**
     * Updates the file object with the specified file version.
     *
     * @param array $version The version to update the file object with.
     */
    protected function updateWithVersion($version)
    {
        // update title (checkFileExtension must be called before setFileName!)
        $this->setTitle($this->checkFileExtension($version["filename"], $this->getTitle()));

        $this->setVersion($version["version"]);
        $this->setMaxVersion($version["max_version"]);
        $this->setFileName($version["filename"]);

        // evaluate mime type (reset file type before)
        $this->setFileType("");
        $this->setFileType($this->guessFileType($version["filename"]));

        // set filesize
        $this->determineFileSize();

        $this->update();

        // refresh preview
        $this->createPreview(true);
    }


    /**
     * Compares two file versions.
     *
     * @param array $v1 First file version to compare.
     * @param array $v2 Second file version to compare.
     *
     * @return int Returns an integer less than, equal to, or greater than zero if the first
     *             argument is considered to be respectively less than, equal to, or greater than
     *             the second.
     */
    public function compareVersions($v1, $v2)
    {
        // v2 - v1 because version should be descending
        return (int) $v2["version"] - (int) $v1["version"];
    }


    /**
     * Parses the info parameters ("info_params") of the specified history entry.
     *
     * @param array $entry The history entry.
     *
     * @return array Returns an array containing the "filename" and "version" contained within the
     *               "info_params".
     */
    private function parseInfoParams($entry)
    {
        $data = explode(",", $entry["info_params"]);

        // bugfix: first created file had no version number
        // this is a workaround for all files created before the bug was fixed
        if (empty($data[1])) {
            $data[1] = "1";
        }

        if (empty($data[2])) {
            $data[2] = "1";
        }

// BEGIN bugfix #27391
        if (sizeof($data) > 3)
        {
          $last = sizeof($data) - 1;
          for ($n = 1; $n < $last - 1; $n++)
          {
            $data[0] .= "," . $data[$n];
          }
          $data[1] = $data[$last - 1];
          $data[2] = $data[$last];
        }
// END bugfix #27391

        $result = array(
            "filename" => $data[0],
            "version" => $data[1],
            "max_version" => $data[2],
            "rollback_version" => "",
            "rollback_user_id" => "",
        );

        // if rollback, the version contains the rollback version as well
        // bugfix mantis 26236: rollback info is read from version to ensure compatibility with older ilias versions
        if ($entry["action"] == "rollback") {
            $tokens = explode("|", $result["version"]);
            if (count($tokens) > 1) {
                $result["version"] = $tokens[0];
                $result["rollback_version"] = $tokens[1];

                if (count($tokens) > 2) {
                    $result["rollback_user_id"] = $tokens[2];
                }
            }
        }

        return $result;
    }


    protected static function handleQuotaUpdate(ilObjFile $a_file)
    {
        include_once "Services/MediaObjects/classes/class.ilObjMediaObject.php";
        $mob = new ilObjMediaObject();

        // file itself could be workspace item
        $parent_obj_ids = array($a_file->getId());

        foreach ($a_file->getUsages() as $item) {
            $parent_obj_id = $mob->getParentObjectIdForUsage($item);
            if ($parent_obj_id
                && !in_array($parent_obj_id, $parent_obj_ids)
            ) {
                $parent_obj_ids[] = $parent_obj_id;
            }
        }

        include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
        ilDiskQuotaHandler::handleUpdatedSourceObject($a_file->getType(), $a_file->getId(), $a_file->getDiskUsage(), $parent_obj_ids);
    }


    /**
     * Creates a preview for the file object.
     *
     * @param bool $force true, to force the creation of the preview; false, to create the preview
     *                    only if the file is newer.
     */
    protected function createPreview($force = false)
    {
        // only normal files are supported
        if ($this->getMode() != self::MODE_OBJECT) {
            return;
        }

        require_once("./Services/Preview/classes/class.ilPreview.php");
        ilPreview::createPreview($this, $force);
    }


    /**
     * Deletes the preview of the file object.
     */
    protected function deletePreview()
    {
        // only normal files are supported
        if ($this->getMode() != self::MODE_OBJECT) {
            return;
        }

        require_once("./Services/Preview/classes/class.ilPreview.php");
        ilPreview::deletePreview($this->getId());
    }


    /**
     * @param bool $a_value
     */
    public function setRating($a_value)
    {
        $this->rating = (bool) $a_value;
    }


    /**
     * @return bool
     */
    public function hasRating()
    {
        return $this->rating;
    }


    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->page_count;
    }


    /**
     * @param int $page_count
     */
    public function setPageCount($page_count)
    {
        $this->page_count = $page_count;
    }


    /**
     * @param int $file_id (part of bugfix mantis 26131)
     *
     * @return array
     */
    private function getArrayForDatabase($file_id = 0)
    {
        if ($file_id == 0) {
            $file_id = $this->getId();
        }
        return [
            'file_id' => ['integer', $file_id],
            'file_name' => ['text', $this->getFileName()],
            'file_type' => ['text', $this->getFileType()],
            'file_size' => ['integer', (int) $this->getFileSize()],
            'version' => ['integer', (int) $this->getVersion()],
            'max_version' => ['integer', (int) $this->getMaxVersion()],
            'f_mode' => ['text', $this->getMode()],
            'page_count' => ['text', $this->getPageCount()],
            'rating' => ['integer', $this->hasRating()],
        ];
    }
}
