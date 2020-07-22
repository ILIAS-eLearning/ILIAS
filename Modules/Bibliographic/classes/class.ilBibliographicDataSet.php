<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Bibliographic dataset class
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class ilBibliographicDataSet extends ilDataSet
{

    /**
     * @var ilDB
     */
    protected $db;
    /**
     * @var array
     */
    protected $data = array();
    /**
     * Maps a given record_field ID (key) to the correct table where the value is stored
     * (il_dcl_stloc(1|2|3)_value)
     *
     * @var array
     */
    protected $record_field_ids_2_storage = array();
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
        parent::__construct();
        $this->db = $ilDB;
        $this->user = $ilUser;
    }


    /**
     * @return array
     */
    public function getSupportedVersions()
    {
        return array( '4.5.0' );
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
                    'is_online' => 'integer',
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
            $a_ids = array( $a_ids );
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
                    if (ilObject::_lookupType($bibl_id) == 'bibl') {
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
     * @param ilImportMapping $a_mapping
     */
    public function importLibraryFile($a_mapping)
    {
        $import_path = $this->getImportDirectory() . "/Modules/Bibliographic/set_1/expDir_1/"
                       . $this->import_bib_object->getFilename();
        $new_id = $this->import_bib_object->getId();
        $new_path = ilUtil::getDataDir() . "/bibl/" . $new_id;
        mkdir($new_path);
        copy($import_path, $new_path . "/" . $this->import_bib_object->getFilename());
    }
}
