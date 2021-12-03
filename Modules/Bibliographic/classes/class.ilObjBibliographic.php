<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\ResourceStorage\Services;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\DI\Container;

/**
 * Class ilObjBibliographic
 * @author  Oskar Truffer <ot@studer-raimann.ch>, Gabriel Comte <gc@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 * @version $Id: class.ilObjBibliographic.php 2012-01-11 10:37:11Z otruffer $
 * @extends ilObject2
 */
class ilObjBibliographic extends ilObject2
{

    /**
     * @var \ilBiblFileReaderFactoryInterface
     */
    protected $bib_filereader_factory;
    /**
     * @var \ilBiblTypeFactoryInterface
     */
    protected $bib_type_factory;
    /**
     * @var \ilBiblEntryFactoryInterface
     */
    protected $bib_entry_factory;
    /**
     * @var \ilBiblFieldFactory
     */
    protected $bib_field_factory;
    /**
     * @var \ilBiblDataFactoryInterface
     */
    protected $bib_data_factory;
    /**
     * @var \ilBiblOverviewModelFactoryInterface
     */
    protected $bib_overview_factory;
    /**
     * @var \ilBiblAttributeFactoryInterface
     */
    protected $bib_attribute_factory;
    /**
     * @var Services
     */
    protected $storage;
    /**
     * @var ilObjBibliographicStakeholder
     */
    protected $stakeholder;
    /**
     * Id of literary articles
     * @var string
     */
    protected $filename;
    /**
     * Id of literary articles
     * @var \ilBiblEntry[]
     */
    protected $entries;
    /**
     * Models describing how the overview of each entry is showed
     * @var bool
     */
    protected $is_online;
    /**
     * @var int
     */
    protected $file_type = 0;
    /**
     * @var ResourceIdentification|null
     */
    protected $resource_id;
    /**
     * @var bool
     */
    protected $is_migrated = false;

    /**
     * initType
     * @return void
     */
    public function initType()
    {
        $this->type = "bibl";
    }

    /**
     * If bibliographic object exists, read it's data from database, otherwise create it
     * @param $existant_bibl_id int is not set when object is getting created
     * @return \ilObjBibliographic
     */
    public function __construct($existant_bibl_id = 0)
    {
        global $DIC;
        /**
         * @var $DIC Container
         */

        $this->storage = $DIC->resourceStorage();
        $this->stakeholder = new ilObjBibliographicStakeholder();

        if ($existant_bibl_id) {
            $this->setId($existant_bibl_id);
            $this->doRead();
        }
        parent::__construct($existant_bibl_id, false);

        $this->bib_type_factory = new ilBiblTypeFactory();
        $this->bib_field_factory = new ilBiblFieldFactory($this->bib_type_factory->getInstanceForType($this->getFileType()));
        $this->bib_overview_factory = new ilBiblOverviewModelFactory();
        $this->bib_entry_factory = new ilBiblEntryFactory(
            $this->bib_field_factory,
            $this->bib_type_factory->getInstanceForType($this->getFileType()),
            $this->bib_overview_factory
        );
        $this->bib_filereader_factory = new ilBiblFileReaderFactory();
        $this->bib_attribute_factory = new ilBiblAttributeFactory($this->bib_field_factory);
    }

    /**
     * handles a FileUpload and returns an IRSS identification string.
     * @param FileUpload $upload
     * @return ResourceIdentification
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     */
    private function handleUpload(FileUpload $upload) : ResourceIdentification
    {
        $upload->process();
        $array_result = $upload->getResults();
        $result = reset($array_result); // FileUpload is the first element

        if ($this->getResourceId()) {
            $this->storage->manage()->appendNewRevision(
                $this->getResourceId(),
                $result,
                $this->stakeholder
            );
            return $this->getResourceId();
        }

        return $this->storage->manage()->upload($result, $this->stakeholder);
    }

    /**
     * Create object
     * @return void
     */
    protected function doCreate()
    {
        global $DIC;

        $upload = $DIC->upload();
        if ($upload->hasUploads() && !$upload->hasBeenProcessed()) {
            $identification = $this->handleUpload($upload);
            $this->setResourceId($identification);
        }

        $DIC->database()->insert(
            "il_bibl_data",
            [
                "id" => ["integer", $this->getId()],
                "filename" => ["text", $this->getFilename()],
                "is_online" => ["integer", $this->getOnline()],
                "file_type" => ["integer",
                                $this->getFilename() ? $this->determineFileTypeByFileName($this->getFilename()) : ""
                ],
                "rid" => ["string", ($rid = $this->getResourceId()) ? $rid->serialize() : ''],
            ]
        );
        $this->parseFileToDatabase();
    }

    protected function doRead()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilBiblData = ilBiblData::where(array('id' => $this->getId()))->first();
        if (!$this->getFilename()) {
            $this->setFilename($ilBiblData->getFilename());
        }
        $this->setFileType($ilBiblData->getFileType());
        $this->setOnline($ilBiblData->getIsOnline());
        if (!empty($rid = $ilBiblData->getResourceId())) {
            $this->setResourceId($this->storage->manage()->find($rid));
            $this->setMigrated(true);
        }
    }

    public function doUpdate()
    {
        global $DIC;

        $upload = $DIC->upload();
        $has_valid_upload = $upload->hasUploads() && !$upload->hasBeenProcessed();

        if ($_POST['override_entries'] && $has_valid_upload) {
            $identification = $this->handleUpload($upload);
            $this->setResourceId($identification);
            if (!$this->isMigrated()) {
                $this->deleteFile();
                $this->setMigrated(true);
            }
        }
        if ($has_valid_upload) {
            // Delete the object, but leave the db table 'il_bibl_data' for being able to update it using WHERE, and also leave the file
            $this->doDelete(true, true);
            $this->parseFileToDatabase();
        }

        $DIC->database()->update(
            "il_bibl_data",
            [
                "filename" => ["text", $this->getFilename()],
                "is_online" => ["integer", $this->getOnline()],
                "file_type" => ["integer", $this->getFileType()],
                "rid" => ["string", ($rid = $this->getResourceId()) ? $rid->serialize() : ''],
            ],
            ["id" => ["integer", $this->getId()]]
        );
    }

    /**
     * @param bool|false $leave_out_il_bibl_data
     * @param bool|false $leave_out_delete_file
     */
    protected function doDelete($leave_out_il_bibl_data = false, $leave_out_delete_file = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        if (!$leave_out_delete_file) {
            $this->deleteFile();
        }
        //il_bibl_attribute
        $ilDB->manipulate(
            "DELETE FROM il_bibl_attribute WHERE il_bibl_attribute.entry_id IN " . "(SELECT il_bibl_entry.id FROM il_bibl_entry WHERE il_bibl_entry.data_id = " . $ilDB->quote(
                $this->getId(),
                "integer"
            ) . ")"
        );
        //il_bibl_entry
        $this->bib_entry_factory->deleteEntryById($this->getId());

        if (!$leave_out_il_bibl_data) {
            //il_bibl_data
            $ilDB->manipulate(
                "DELETE FROM il_bibl_data WHERE id = " . $ilDB->quote($this->getId(), "integer")
            );
        }
        // delete history entries
        ilHistory::_removeEntriesForObject($this->getId());
    }

    /**
     * @return string the folder is: bibl/$id
     */
    public function getFileDirectory()
    {
        return "{$this->getType()}/{$this->getId()}";
    }

    /**
     * @param \ILIAS\FileUpload\FileUpload $upload
     * @depracated
     */
    protected function moveUploadedFile(\ILIAS\FileUpload\FileUpload $upload)
    {
        /**
         * @var $result \ILIAS\FileUpload\DTO\UploadResult
         */
        $result = array_values($upload->getResults())[0];
        if ($result->isOK()) {
            $this->deleteFile();
            $upload->moveFilesTo($this->getFileDirectory(), \ILIAS\FileUpload\Location::STORAGE);
            $this->setFilename($result->getName());
        }
    }

    /**
     * @param $file_to_copy
     */
    private function copyFile($file_to_copy)
    {
        $target = $this->getFileDirectory() . '/' . basename($file_to_copy);
        $this->getFileSystem()->copy($file_to_copy, $target);
    }

    /**
     * @return bool
     */
    protected function deleteFile()
    {
        $path = $this->getFileDirectory();
        try {
            $this->getFileSystem()->deleteDir($path);
        } catch (\ILIAS\Filesystem\Exception\IOException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return \ILIAS\Filesystem\Filesystem
     */
    private function getFileSystem()
    {
        global $DIC;

        return $DIC["filesystem"]->storage();
    }

    /**
     * @param bool $without_filename
     * @return string
     */
    public function getFilePath($without_filename = false)
    {
        $file_name = $this->getFilename();

        if ($without_filename) {
            return substr($file_name, 0, strrpos($file_name, DIRECTORY_SEPARATOR));
        } else {
            return $file_name;
        }
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @return string returns the absolute filepath of the bib/ris file. it's build as follows:
     *                /bibl/$id/$filename
     */
    public function getFileAbsolutePath()
    {
        return $this->getFileDirectory() . DIRECTORY_SEPARATOR . $this->getFilename();
    }

    public function getLegacyAbsolutePath()
    {
        $stream = ($this->isMigrated()) ?
            $this->storage->consume()->stream($this->getResourceId())->getStream() :
            $this->getFileSystem()->readStream($this->getFileAbsolutePath());

        return $stream->getMetadata('uri');
    }

    /**
     * @return string
     * @deprecated use type factory instead of string representation
     */
    public function getFileTypeAsString()
    {
        $type = $this->getFileType();

        return $this->bib_type_factory->getInstanceForType($type)->getStringRepresentation();
    }

    /**
     * @return int
     */
    public function getFileType()
    {
        $filename = $this->getFilename();
        if ($filename === null) {
            return ilBiblTypeFactoryInterface::DATA_TYPE_BIBTEX;
        }
        $instance = $this->bib_type_factory->getInstanceForFileName($filename);

        return $instance->getId();
    }

    /**
     * Clone BIBL
     * @param ilObjBibliographic $new_obj
     * @param                    $a_target_id
     * @param int                $a_copy_id copy id
     * @return ilObjPoll
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null, $a_omit_tree = false)
    {
        assert($new_obj instanceof ilObjBibliographic);
        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOnline($this->getOnline());
        }

        $new_obj->cloneStructure($this->getId());

        $new_obj->parseFileToDatabase();

        return $new_obj;
    }

    /**
     * @description Attention only use this for objects who have not yet been created (use like: $x
     *              = new ilObjDataCollection;
     *              $x->cloneStructure($id))
     * @param int $original_id The original ID of the dataselection you want to clone it's structure
     * @return void
     */
    public function cloneStructure($original_id)
    {
        $original = new ilObjBibliographic($original_id);
        $this->setFilename($original->getFilename());
        $this->setDescription($original->getDescription());
        $this->setTitle($original->getTitle());
        $this->setType($original->getType());
        $identification = $original->getResourceId();
        if ($identification instanceof ResourceIdentification) {
            $new_identification = $this->storage->manage()->clone($identification);
            $this->setResourceId($new_identification);
        } else {
            $this->copyFile($original->getFileAbsolutePath());
        }
        $this->parseFileToDatabase();
        $this->setMigrated($original->isMigrated());
        $this->doUpdate();
    }

    /**
     * Reads out the source file and writes all entries to the database
     * @return void
     */
    public function parseFileToDatabase()
    {
        //Read File
        $type = $this->getFileType();
        $reader = $this->bib_filereader_factory->getByType(
            $type,
            $this->bib_entry_factory,
            $this->bib_field_factory,
            $this->bib_attribute_factory
        );
        $reader->readContent($this->getResourceId());
        $this->entries = $reader->parseContentToEntries($this);
    }

    /**
     * @param int $file_type
     */
    public function setFileType($file_type)
    {
        $this->file_type = $file_type;
    }

    /**
     * @param $a_online
     */
    public function setOnline($a_online)
    {
        $this->is_online = $a_online;
    }

    /**
     * @return bool
     */
    public function getOnline()
    {
        return $this->is_online;
    }

    /**
     * @param ResourceIdentification $identification
     */
    public function setResourceId(ResourceIdentification $identification) : void
    {
        $this->resource_id = $identification;
    }

    /**
     * @return ResourceIdentification
     */
    public function getResourceId() : ?ResourceIdentification
    {
        return $this->resource_id;
    }

    public function getStorageId() : ?string
    {
        if (!$this->getResourceId() instanceof ResourceIdentification) {
            return '-';
        }
        return $this->storage->manage()->getResource($this->getResourceId())->getStorageID();
    }

    /**
     * @return bool
     */
    public function isMigrated() : bool
    {
        return $this->is_migrated;
    }

    /**
     * @param bool $migrated
     */
    public function setMigrated(bool $migrated) : void
    {
        $this->is_migrated = $migrated;
    }

    /**
     * @param $filename
     * @return int
     */
    public function determineFileTypeByFileName($filename)
    {
        return $this->bib_type_factory->getInstanceForFileName($filename)->getId();
    }
}
