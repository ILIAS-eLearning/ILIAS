<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;
use ILIAS\ResourceStorage\Resource\ResourceBuilder;
use ILIAS\ResourceStorage\StorageHandler\FileSystemStorageHandler;
use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\FileUpload\Location;
use ILIAS\ResourceStorage\Revision\Repository\RevisionARRepository;
use ILIAS\ResourceStorage\Resource\Repository\ResourceARRepository;
use ILIAS\ResourceStorage\Information\Repository\InformationARRepository;
use ILIAS\ResourceStorage\Stakeholder\Repository\StakeholderARRepository;
use ILIAS\ResourceStorage\Lock\LockHandlerilDB;
use ILIAS\Filesystem\Stream\Streams;

/**
 * Bibliographic dataset class
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilBibliographicDataSet extends ilDataSet
{

    /**
     * @var ilDBInterface
     */
    protected $db;
    /**
     * @var \ILIAS\ResourceStorage\Services
     */
    protected $storage;
    /**
     * @var ilObjBibliographicStakeholder
     */
    protected $stakeholder;
    /**
     * @var array
     */
    protected $data = array();
    /**
     * @var ilObjBibliographic
     */
    protected $import_bib_object;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var array
     */
    protected $import_temp_refs = array();
    /**
     * @var array
     */
    protected $import_temp_refs_props = array();


    public function __construct()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $IRSS = $DIC['resource_storage'];
        parent::__construct();
        $this->db = $ilDB;
        $this->user = $ilUser;
        $this->storage = $IRSS;
        $this->stakeholder = new ilObjBibliographicStakeholder();
    }


    /**
     * @return array
     */
    public function getSupportedVersions()
    {
        return array('4.5.0');
    }


    /**
     * @param string $a_entity
     * @param string $a_schema_version
     *
     * @return string
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return 'http://www.ilias.de/xml/Modules/Bibliographic/' . $a_entity;
    }


    /**
     * @param string          $a_entity
     * @param                 $a_types
     * @param array           $a_rec
     * @param ilImportMapping $a_mapping
     * @param string          $a_schema_version
     */
    public function importRecord($a_entity, $a_types, $a_rec, $a_mapping, $a_schema_version)
    {
        switch ($a_entity) {
            case 'bibl':
                if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_rec['id'])) {
                    // container content
                    $new_obj = ilObjectFactory::getInstanceByObjId($new_id, false);
                } else {
                    $new_obj = new ilObjBibliographic();
                }
                /**
                 * @var $new_obj ilObjBibliographic
                 */
                $new_obj->setTitle($a_rec['title']);
                $new_obj->setDescription($a_rec['description']);
                $new_obj->setFilename($a_rec['fileName']);
                $new_obj->setOnline(false);
                if (!$new_obj->getId()) {
                    $new_obj->create();
                }
                $this->import_bib_object = $new_obj;
                $a_mapping->addMapping('Modules/Bibliographic', 'bibl', $a_rec['id'], $new_obj->getId());
                $this->importLibraryFile($a_mapping);
                break;
        }
    }


    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     *
     * @param string $a_entity
     * @param string $a_version
     *
     * @return array
     */
    protected function getTypes($a_entity, $a_version)
    {
        switch ($a_entity) {
            case 'bibl':
                return array(
                    "id" => "integer",
                    "title" => "text",
                    "description" => "text",
                    "filename" => "text",
                    "is_online" => "integer",
                );
            default:
                return array();
        }
    }


    /**
     * Return dependencies form entities to other entities (in our case these are all the DB
     * relations)
     *
     * @param string $a_entity
     * @param string $a_version
     * @param array  $a_rec
     * @param array  $a_ids
     *
     * @return array
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        return false;
    }


    /**
     * Read data from Cache for a given entity and ID(s)
     *
     * @param string $a_entity
     * @param string $a_version
     * @param array  $a_ids one or multiple ids
     */
    public function readData($a_entity, $a_version, $a_ids)
    {
        $this->data = array();
        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        $this->_readData($a_entity, $a_ids);
    }


    /**
     * Build data array, data is read from cache except bibl object itself
     *
     * @param string $a_entity
     * @param array  $a_ids
     */
    protected function _readData($a_entity, $a_ids)
    {
        switch ($a_entity) {
            case 'bibl':
                foreach ($a_ids as $bibl_id) {
                    if (ilObject::_lookupType($bibl_id) === 'bibl') {
                        $obj = new ilObjBibliographic($bibl_id);
                        $data = array(
                            'id' => $bibl_id,
                            'title' => $obj->getTitle(),
                            'description' => $obj->getDescription(),
                            'fileName' => $obj->getFilename(),
                            'is_online' => $obj->getOnline(),
                        );
                        $this->data[] = $data;
                    }
                }
                break;
            default:
        }
    }


    /**
     *
     * @param int $a_id
     */
    public function exportLibraryFile($a_id)
    {
        $obj = new ilObjBibliographic($a_id);
        $fileAbsolutePath = $obj->getLegacyAbsolutePath();
        copy($fileAbsolutePath, $this->absolute_export_dir . "/" . $obj->getFilename());
    }


    /**
     * @param ilImportMapping $a_mapping (what's it for?)
     */
    public function importLibraryFile($a_mapping) : void
    {
        $bib_id      = $this->import_bib_object->getId();
        $filename    = $this->import_bib_object->getFilename();
        $import_path = $this->getImportDirectory() . "/Modules/Bibliographic/set_1/expDir_1/" . $filename;

        // create new resource from stream
        $stream = Streams::ofResource(@fopen($import_path, 'rb'));
        $identification = $this->storage->manage()->stream($stream, $this->stakeholder, $filename);

        // insert rid of the new resource into the data table
        $this->db->manipulateF(
            'UPDATE `il_bibl_data` SET `rid` = %s WHERE `id` = %s;',
            ['text', 'integer'], [
                $identification->serialize(),
                $bib_id,
            ]
        );
    }
}
