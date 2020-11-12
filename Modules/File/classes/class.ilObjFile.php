<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Util\LegacyPathHelper;
use ILIAS\FileUpload\Location;
use ILIAS\DI\Container;
use ILIAS\File\Sanitation\FilePathSanitizer;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;

/**
 * Class ilObjFile
 * @author  Sascha Hofmann <shofmann@databay.de>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version $Id$
 * @ingroup ModulesFile
 */
class ilObjFile extends ilObject2 implements ilObjFileImplementationInterface
{
    use ilObjFileMetadata;
    use ilObjFileUsages;

    public const MODE_FILELIST = "filelist";
    public const MODE_OBJECT = "object";

    /**
     * @var ilObjFileImplementationInterface
     */
    protected $implementation;
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
    protected $filesize;
    /**
     * @var int
     */
    protected $page_count = 0;
    /**
     * @var bool
     */
    protected $rating = false;
    /**
     * @var \ilLogger
     */
    protected $log;
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
    protected $action;
    /**
     * @var string|null
     */
    protected $resource_id;

    /**
     * @var string
     */
    public $mode = self::MODE_OBJECT;
    /**
     * @var \ILIAS\ResourceStorage\Manager\Manager
     */
    protected $manager;

    /**
     * ilObjFile constructor.
     * @param int  $a_id                ID of the object, ref_id or obj_id possible
     * @param bool $a_call_by_reference defines the $a_id a ref_id
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->manager =  $DIC->resourceStorage()->manage();
        $this->version = 0;
        $this->max_version = 0;


        $this->log = ilLoggerFactory::getLogger('file');

        parent::__construct($a_id, $a_call_by_reference);
    }

    public function appendStream(FileStream $stream, string $title, bool $keep_existing = true) : int
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->manager = $DIC->resourceStorage()->manage();
        if ($this->getResourceId() && $i = $this->manager->find($this->getResourceId())) {
            $revision = $this->manager->appendNewRevisionFromStream($i, $stream, new ilObjFileStakeholder(), $title);
        } else {
            $i = $this->manager->stream($stream, new ilObjFileStakeholder(), $title);
            $revision = $this->manager->getCurrentRevision($i);
            $this->setResourceId($i->serialize());
        }

        $revision->getInformation()->setTitle('this is a title.docx');
        $this->manager->updateRevision($revision);

        $this->setTitle($title);
        $this->setVersion($revision->getVersionNumber());
        $this->update();

        return $revision->getVersionNumber();
    }

    private function appendUpload(UploadResult $result, bool $keep_existing = true) : int
    {

    }

    /**
     * @param null $a_hist_entry_id
     * @return string
     * @deprecated
     */
    public function getFile($a_hist_entry_id = null)
    {
        return $this->implementation->getFile($a_hist_entry_id);
    }

    public function getDirectory($a_version = 0)
    {
        return $this->implementation->getDirectory($a_version);
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($a_version)
    {
        $this->version = $a_version;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }

    /**
     * @param string $a_name
     */
    public function setFileName($a_name)
    {
        $this->filename = $a_name;
    }

    /**
     * @param bool $a_value
     */
    public function setRating($a_value)
    {
        $this->rating = (bool) $a_value;
    }

    /**
     * @param string|null $resource_id
     * @return ilObjFile
     */
    public function setResourceId(?string $resource_id) : ilObjFile
    {
        $this->resource_id = $resource_id;
        return $this;
    }

    public function getResourceId() : ?string
    {
        return $this->resource_id;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param $a_mode self::MODE_FILELIST or self::MODE_OBJECT
     */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }

    public function getFileSize()
    {
        return $this->filesize;
    }

    /**
     * @param $a_size
     */
    public function setFileSize($a_size)
    {
        $this->filesize = $a_size;
    }

    /**
     * @return string
     */
    public function getFileType()
    {
        return $this->filetype;
    }

    /**
     * @param string $a_type
     */
    public function setFileType($a_type)
    {
        $this->filetype = $a_type;
    }

    /**
     * @return bool
     */
    public function hasRating()
    {
        return $this->rating;
    }

    public function getMaxVersion()
    {
        return $this->max_version;
    }

    public function setMaxVersion($a_max_version)
    {
        $this->max_version = $a_max_version;
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
     * @return string
     * @deprecated
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param $a_action
     * @deprecated
     */
    public function setAction($a_action)
    {
        $this->action = $a_action;
    }


    // CRUD

    /**
     * @param false $a_upload
     */
    protected function doCreate($a_upload = false)
    {
        $this->createProperties($a_upload);
    }

    protected function doRead()
    {
        global $DIC;
        /**
         * @var $DIC Container
         */

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
        $this->setPageCount($row->page_count);
        $this->setResourceId($row->rid);

        if ($this->resource_id) {
            $id = $DIC->resourceStorage()->manage()->find($this->resource_id);
            $resource = $DIC->resourceStorage()->manage()->getResource($id);
            $this->implementation = new ilObjFileImplementationStorage($resource);
        } else {
            $this->implementation = new ilObjFileImplementationLegacy($this->getId(), $this->getVersion(),
                $this->getFileName());
            $s = new FilePathSanitizer($this);
            $s->sanitizeIfNeeded();
        }
    }

    protected function doCloneObject($a_new_obj, $a_target_id, $a_copy_id = 0)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $a_new_obj->createDirectory();
        $this->cloneMetaData($a_new_obj);

        // Copy all file versions
        ilUtil::rCopy($this->getDirectory(), $a_new_obj->getDirectory());

        // object created now copy other settings
        $query = "INSERT INTO file_data (file_id,file_name,file_type,file_size,version,rating,f_mode) VALUES ("
            . $ilDB->quote($a_new_obj->getId(), 'integer') . ","
            . $ilDB->quote($this->getFileName(), 'text') . ","
            . $ilDB->quote($this->getFileType(), 'text') . ","
            . $ilDB->quote((int) $this->getFileSize(), 'integer') . ", "
            . $ilDB->quote($this->getVersion(), 'integer') . ", "
            . $ilDB->quote($this->hasRating(), 'integer') . ", "
            . $ilDB->quote($this->getMode(), 'text') . ")";
        $res = $ilDB->manipulate($query);

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

        return true;
    }

    protected function beforeUpdate()
    {
        // no meta data handling for file list files
        if ($this->getMode() != self::MODE_FILELIST) {
            $this->updateMetaData();
        }

        return true;
    }

    protected function beforeDelete()
    {
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
     * @return array
     */
    private function getArrayForDatabase() : array
    {
        return [
            'file_id' => ['integer', $this->getId()],
            'file_name' => ['text', $this->getFileName()],
            'file_type' => ['text', $this->getFileType()],
            'file_size' => ['integer', (int) $this->getFileSize()],
            'version' => ['integer', (int) $this->getVersion()],
            'max_version' => ['integer', (int) $this->getMaxVersion()],
            'f_mode' => ['text', $this->getMode()],
            'page_count' => ['text', $this->getPageCount()],
            'rating' => ['integer', $this->hasRating()],
            'rid' => ['text', $this->resource_id],
        ];
    }

    public function initType()
    {
        $this->type = "file";
    }

    public function createDirectory()
    {
        // we no longer create directories
    }

    public function raiseUploadError($raise = false)
    {
        // we no longer support that
    }

    // Upload Handling
    public function replaceFile($a_upload_file, $a_filename)
    {
        return null;
    }

    public function getUploadFile($a_upload_file, $a_filename, $a_prevent_preview = false)
    {
        return null;
    }

    public function addNewsNotification($a_lang_var)
    {
        if ($this->isHidden()) {
            return;
        }

        global $DIC;

        // Add Notification to news
        $news_item = new ilNewsItem();
        $news_item->setContext($this->getId(), $this->getType());
        $news_item->setPriority(NEWS_NOTICE);
        $news_item->setTitle($a_lang_var);
        $news_item->setContentIsLangVar(true);
        if ($this->getDescription() !== "") {
            $news_item->setContent("<p>" . $this->getDescription() . "</p>");
        }
        $news_item->setUserId($DIC->user()->getId());
        $news_item->setVisibility(NEWS_USERS);
        $news_item->create();
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isHidden()
    {
        return ilObjFileAccess::_isFileHidden($this->getTitle());
    }

    /**
     * @param $a_upload_file
     * @param $a_filename
     * @return bool
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    public function addFileVersion($a_upload_file, $a_filename)
    {
        /**
         * @var $DIC Container
         */
        global $DIC;
        $id = $DIC->resourceStorage()->manage()->find($this->getResourceId());
        if ($id) {
            $upload = $DIC->upload()->getResults()[$a_upload_file];
            $DIC->resourceStorage()->manage()->appendNewRevision($id, $upload, new ilObjFileStakeholder(),
                $a_filename);
        }
        $this->addNewsNotification("file_updated");
//        $this->createPreview($this->getVersion() > 1);

        return true;
    }

    /**
     * @ineritdoc
     */
    public function clearDataDirectory()
    {
        $this->implementation->clearDataDirectory();
    }

    // FSX

    /**
     * @ineritdoc
     */
    public function deleteVersions($a_hist_entry_ids = null)
    {
        $this->implementation->deleteVersions($a_hist_entry_ids);
    }

    /**
     * @param null $a_hist_entry_id
     * @return bool
     */
    public function sendFile($a_hist_entry_id = null) : void
    {
        $this->implementation->sendFile($a_hist_entry_id);
    }

    /**
     * @return bool
     * @deprecated
     */
    public function isInline()
    {
        return ilObjFileAccess::_isFileInline($this->getTitle());
    }

    /**
     * @param $a_target_dir
     * @deprecated
     */
    public function export($a_target_dir)
    {
        $this->implementation->export($a_target_dir);
    }

    /**
     * storeUnzipedFile
     * Stores Files unzipped from uploaded archive in filesystem
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
     * @param null $version_ids
     * @return array|ilObjFileVersion[]
     */
    public function getVersions($version_ids = null) : array
    {
        return $this->implementation->getVersions($version_ids);
    }

    /**
     * Makes the specified version the current one and returns theSummary of rollbackVersion
     * @param int $version_id The id of the version to make the current one.
     * @return array The new actual version.
     */
    public function rollback($version_id)
    {
        global $DIC;

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
        ilHistory::_createEntry($this->getId(), "rollback", $source["filename"] . ","
            . $new_version_nr . ","
            . $this->getMaxVersion() . "|"
            . $source["version"] . "|"
            . $ilUser->getId());

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
     * @param int $version_id
     * @return array
     * @deprecated
     */
    public function getSpecificVersion($version_id)
    {
        return $this->implementation->getSpecificVersion($version_id);
    }

    /**
     * Updates the file object with the specified file version.
     * @param array $version The version to update the file object with.
     */
    // FSX
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

        $this->update();

        // refresh preview
        $this->createPreview(true);
    }

    /**
     * @param $new_filename
     * @param $new_title
     * @return string
     * @deprecated
     */
    public function checkFileExtension($new_filename, $new_title)
    {
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
     * @return string
     * @deprecated
     */
    public function getFileExtension()
    {
        return $this->implementation->getFileExtension();
    }

}
