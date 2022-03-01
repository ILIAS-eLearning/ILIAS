<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\File\Sanitation\FilePathSanitizer;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Policy\FileNamePolicyException;

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
    use ilObjFilePreviewHandler;
    use ilObjFileNews;

    public const MODE_FILELIST = "filelist";
    public const MODE_OBJECT = "object";

    /**
     * @var ilObjFileImplementationInterface
     */
    protected $implementation;

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

// ABSTRACT
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
    protected $version = 1;
    /**
     * @var int
     */
    protected $max_version = 1;
    /**
     * @var string
     */
    protected $action;
// ABSTRACT

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
     * @var \ILIAS\FileUpload\FileUpload
     */
    protected $upload;
    /**
     * @var ilObjFileStakeholder
     */
    protected $stakeholder;

    /**
     * ilObjFile constructor.
     * @param int  $a_id                ID of the object, ref_id or obj_id possible
     * @param bool $a_call_by_reference defines the $a_id a ref_id
     */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */
        $this->manager = $DIC->resourceStorage()->manage();
        $this->stakeholder = new ilObjFileStakeholder($DIC->user()->getId());
        $this->upload = $DIC->upload();
        $this->version = 0;
        $this->max_version = 0;
        $this->log = ilLoggerFactory::getLogger('file');

        parent::__construct($a_id, $a_call_by_reference);
    }

    protected function initImplementation() : void
    {
        if ($this->resource_id && ($id = $this->manager->find($this->resource_id)) !== null) {
            $resource = $this->manager->getResource($id);
            $this->implementation = new ilObjFileImplementationStorage($resource);
            $this->setMaxVersion($resource->getMaxRevision());
            $this->setVersion($resource->getMaxRevision());
        } else {
            $this->implementation = new ilObjFileImplementationLegacy(
                (int) $this->getId(),
                (int) $this->getVersion(),
                (string) $this->getFileName()
            );
            $s = new FilePathSanitizer($this);
            $s->sanitizeIfNeeded();
        }
    }

    private function updateObjectFromRevision(Revision $r, bool $create_previews = true) : void
    {
        $this->setTitle($r->getTitle());
        $this->setFileName($r->getInformation()->getTitle());
        $this->setVersion($r->getVersionNumber());
        $this->setMaxVersion($r->getVersionNumber());
        $this->setFileSize($r->getInformation()->getSize());
        $this->setFileType($r->getInformation()->getMimeType());
        $this->update();
        if ($create_previews) {
            $this->createPreview(true);
        }
    }

    private function appendSuffixToTitle(string $title, string $filename) : string
    {
        // bugfix mantis 0026160 && 0030391
        $uploaded_suffix = pathinfo($filename, PATHINFO_EXTENSION);
        $input_title = pathinfo($title, PATHINFO_FILENAME) . '.' . $uploaded_suffix;

        return $input_title;
    }

    /**
     * @throws FileNamePolicyException
     */
    public function appendStream(FileStream $stream, string $title) : int
    {
        // $title = $this->appendSuffixToTitle($title, $stream->getMetadata(['uri']));
        if ($this->getResourceId() && $i = $this->manager->find($this->getResourceId())) {
            $revision = $this->manager->appendNewRevisionFromStream($i, $stream, $this->stakeholder, $title);
        } else {
            $i = $this->manager->stream($stream, $this->stakeholder, $title);
            $revision = $this->manager->getCurrentRevision($i);
            $this->setResourceId($i->serialize());
            $this->initImplementation();
        }
        $this->updateObjectFromRevision($revision);

        return $revision->getVersionNumber();
    }

    /**
     * @throws FileNamePolicyException
     */
    public function appendUpload(UploadResult $result, string $title) : int
    {
        $title = $this->appendSuffixToTitle($title, $result->getName());
        if ($this->getResourceId() && $i = $this->manager->find($this->getResourceId())) {
            $revision = $this->manager->appendNewRevision($i, $result, $this->stakeholder, $title);
        } else {
            $i = $this->manager->upload($result, $this->stakeholder, $title);
            $revision = $this->manager->getCurrentRevision($i);
            $this->setResourceId($i->serialize());
            $this->initImplementation();
        }
        if ($result->getMetaData()->has(ilCountPDFPagesPreProcessors::PAGE_COUNT)) {
            $this->setPageCount($result->getMetaData()->get(ilCountPDFPagesPreProcessors::PAGE_COUNT));
        }
        $this->updateObjectFromRevision($revision);

        return $revision->getVersionNumber();
    }

    /**
     * @throws FileNamePolicyException
     */
    public function replaceWithStream(FileStream $stream, string $title) : int
    {
        if ($this->getResourceId() && $i = $this->manager->find($this->getResourceId())) {
            $revision = $this->manager->replaceWithStream($i, $stream, $this->stakeholder, $title);
        } else {
            throw new LogicException('only files with existing resource and revision can be replaced');
        }
        $this->updateObjectFromRevision($revision);

        return $revision->getVersionNumber();
    }

    /**
     * @throws FileNamePolicyException
     */
    public function replaceWithUpload(UploadResult $result, string $title) : int
    {
        $title = $this->appendSuffixToTitle($title, $result->getName());
        if ($this->getResourceId() && $i = $this->manager->find($this->getResourceId())) {
            $revision = $this->manager->replaceWithUpload($i, $result, $this->stakeholder, $title);
        } else {
            throw new LogicException('only files with existing resource and revision can be replaced');
        }
        if ($result->getMetaData()->has(ilCountPDFPagesPreProcessors::PAGE_COUNT)) {
            $this->setPageCount($result->getMetaData()->get(ilCountPDFPagesPreProcessors::PAGE_COUNT));
        }
        $this->updateObjectFromRevision($revision);

        return $revision->getVersionNumber();
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
        return $this->resource_id ?? '-';
    }

    public function getStorageID() : ?string
    {
        return $this->implementation->getStorageID();
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
        $this->notifyCreation($this->getId(), $this->getDescription());
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

        $this->initImplementation();
    }

    protected function doCloneObject($new_object, $a_target_id, $a_copy_id = 0)
    {
        /**
         * @var $new_object ilObjFile
         */
        $this->cloneMetaData($new_object);

        // object created now copy other settings
        $this->db->manipulateF(
            "INSERT INTO file_data (file_id, file_name, file_type, file_size, version, rating, f_mode) VALUES (%s, %s, %s, %s, %s, %s, %s)",
            [
                'integer', // file_id
                'text', // file_name
                'text', // file_type
                'integer', // file_size
                'integer', // version
                'integer', // rating
                'integer' // f_mode
            ],
            [
                (int) $new_object->getId(),
                $this->getFileName(),
                $this->getFileType(),
                (int) $this->getFileSize(),
                (int) $this->getVersion(),
                (int) $this->hasRating(),
                (int) $this->getMode()
            ]
        );

        // Copy Resource
        if ($this->resource_id
            && ($identification = $this->manager->find($this->resource_id)) instanceof ResourceIdentification) {
            $new_resource_identification = $this->manager->clone($identification);
            $new_current_revision = $this->manager->getCurrentRevision($new_resource_identification);
            $new_object->setResourceId($new_resource_identification->serialize());
            $new_object->initImplementation();
            $new_object->updateObjectFromRevision($new_current_revision, false); // Previews are already copied in 453
            $new_object->setTitle($this->getTitle()); // see https://mantis.ilias.de/view.php?id=31375
            $new_object->update();
        } else {
            // migrate
            global $DIC;
            $migration = new ilFileObjectToStorageMigrationRunner(
                $DIC->fileSystem()->storage(),
                $DIC->database(),
                rtrim(CLIENT_DATA_DIR, "/") . '/ilFile/migration_log.csv'
            );
            $migration->setMigrateToNewObjectId((int) $new_object->getId());
            $migration->migrate(new ilFileObjectToStorageDirectory($this->getId(), $this->getDirectory()));
        }

        // copy all previews
        ilPreview::copyPreviews($this->getId(), $new_object->getId());

        // Copy learning progress settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_object->getId());
        unset($obj_settings);

        return $new_object;
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

        $this->notifyUpdate($this->getId(), $this->getDescription());

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

        // delete file data entry
        $DIC->database()->manipulateF("DELETE FROM file_data WHERE file_id = %s", ['integer'], [$this->getId()]);

        // delete history entries
        ilHistory::_removeEntriesForObject($this->getId());

        // delete meta data
        if ($this->getMode() != self::MODE_FILELIST) {
            $this->deleteMetaData();
        }

        // delete preview
        $this->deletePreview();

        // delete resource
        $identification = $this->getResourceId();
        if ($identification && $identification != '-') {
            $resource = $this->manager->find($identification);
            if ($resource !== null) {
                $this->manager->remove($resource, $this->stakeholder);
            }
        }
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
            'rid' => ['text', $this->resource_id ?? ''],
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

    private function prepareUpload() : void
    {
        if (true !== $this->upload->hasBeenProcessed()) {
            if (defined('PATH_TO_GHOSTSCRIPT') && PATH_TO_GHOSTSCRIPT !== "") {
                $this->upload->register(new ilCountPDFPagesPreProcessors());
            }

            $this->upload->process();
        }
    }

    /**
     * @description This Method is used to append a fileupload by it's POST-name to the current ilObjFile
     * @deprecated
     * @see         appendUpload(), appendStream()
     */
    public function getUploadFile($a_upload_file, string $title, bool $a_prevent_preview = false) : bool
    {
        $this->prepareUpload();

        $results = $this->upload->getResults();
        $upload = $results[$a_upload_file];

        $this->appendUpload($upload, $title);

        return true;
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
     * @ineritdoc
     * @deprecated
     */
    public function clearDataDirectory()
    {
        $this->implementation->clearDataDirectory();
    }

    /**
     * @ineritdoc
     * @deprecated
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
     * @deprecated
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
     * Makes the specified version the current one
     * @param int $version_id The id of the version to make the current one.
     */
    public function rollback(int $version_id) : void
    {
        if ($this->getResourceId() && $i = $this->manager->find($this->getResourceId())) {
            $this->manager->rollbackRevision($i, $version_id);
            $latest_revision = $this->manager->getCurrentRevision($i);
            $this->updateObjectFromRevision($latest_revision);
        } else {
            throw new LogicException('only files with existing resource and revision can be replaced');
        }
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
