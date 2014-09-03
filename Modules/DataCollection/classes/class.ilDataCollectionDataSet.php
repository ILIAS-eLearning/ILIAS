<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/DataSet/classes/class.ilDataSet.php");
require_once('class.ilDataCollectionCache.php');

/**
 * DataCollection dataset class
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 *
 */
class ilDataCollectionDataSet extends ilDataSet
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
     * Maps a given record_field ID (key) to the correct table where the value is stored (il_dcl_stloc(1|2|3)_value
     *
     * @var array
     */
    protected $record_field_ids_2_storage = array();


    public function __construct() {
        global $ilDB;
        parent::__construct();
        $this->db = $ilDB;
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
     * @return string
     */
    public function getXmlNamespace($a_entity, $a_schema_version)
    {
        return 'http://www.ilias.de/xml/Modules/DataCollection/' . $a_entity;
    }


    /**
     * Map XML attributes of entities to datatypes (text, integer...)
     *
     * @param string $a_entity
     * @param string $a_version
     * @return array
     */
    protected function getTypes($a_entity, $a_version)
    {
        if ($a_entity == 'dcl') {
            return array(
                "id" => "integer",
                "title" => "text",
                "description" => "text",
                'main_table_id' => 'integer',
                'is_online' => 'integer',
                'rating' => 'integer',
                'public_notes' => 'integer',
                'approval' => 'integer',
                'notification' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_table') {
            return array(
                'id' => 'integer',
                'obj_id' => 'integer',
                'title' => 'text',
                'add_perm' => 'integer',
                'edit_perm' => 'integer',
                'delete_perm' => 'integer',
                'edit_by_owner' => 'integer',
                'limited' => 'integer',
                'limit_start' => 'text',
                'limit_end' => 'text',
                'is_visible' => 'integer',
                'export_enabled' => 'integer',
                'default_sort_field_id' => 'text',
                'default_sort_field_order' => 'text',
                'description' => 'text',
                'public_comments' => 'integer',
                'view_own_records_perm' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_field') {
            return array(
                'id' => 'integer',
                'table_id' => 'integer',
                'title' => 'text',
                'description' => 'text',
                'datatype_id' => 'integer',
                'required' => 'integer',
                'is_unique' => 'integer',
                'is_locked' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_field_prop') {
            return array(
                'id' => 'integer',
                'field_id' => 'integer',
                'datatype_prop_id' => 'integer',
                'value' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_record') {
            return array(
                'id' => 'integer',
                'table_id' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_record_field') {
            return array(
                'id' => 'integer',
                'record_id' => 'integer',
                'field_id' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_stloc1_value') {
            return array(
                'id' => 'integer',
                'record_field_id' => 'integer',
                'value' => 'text',
            );
        }

        if ($a_entity == 'il_dcl_stloc2_value') {
            return array(
                'id' => 'integer',
                'record_field_id' => 'integer',
                'value' => 'integer',
            );
        }

        if ($a_entity == 'il_dcl_stloc3_value') {
            return array(
                'id' => 'integer',
                'record_field_id' => 'integer',
                'value' => 'text',
            );
        }

        if ($a_entity == 'il_dcl_view') {
            return array(
                'id' => 'integer',
                'table_id' => 'integer',
                'type' => 'integer',
                'formtype' => 'integer',
            );
        }
    }

    /**
     * Return dependencies form entities to other entities (in our case these are all the DB relations)
     *
     * @param $a_entity
     * @param $a_version
     * @param $a_rec
     * @param $a_ids
     * @return array
     */
    protected function getDependencies($a_entity, $a_version, $a_rec, $a_ids)
    {
        if (!$a_rec && !$a_ids) {
            return false;
        }
        switch ($a_entity) {
            case 'dcl':
                $ids = array();
                $set = $this->db->query('SELECT id FROM il_dcl_table WHERE obj_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                while ($rec = $this->db->fetchObject($set)) {
                    $ids[] = $rec->id;
                }
                return array(
                    'il_dcl_table' => array('ids' => $ids),
                );
            case 'il_dcl_table':
                $record_ids = array();
//                $set = $this->db->query('SELECT id FROM il_dcl_record WHERE table_id IN (' . implode(',', $a_ids) . ')');
                $set = $this->db->query('SELECT id FROM il_dcl_record WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                while ($rec = $this->db->fetchObject($set)) {
                    $record_ids[] = $rec->id;
                }
                $field_ids = array();
//                $set = $this->db->query('SELECT id FROM il_dcl_field WHERE table_id IN (' . implode(',', $a_ids) . ')');
                $set = $this->db->query('SELECT id FROM il_dcl_field WHERE table_id = ' . $this->db->quote($a_rec['id'], 'integer'));
                while ($rec = $this->db->fetchObject($set)) {
                    $field_ids[] = $rec->id;
                }
                return array(
                    'il_dcl_record' => array('ids' => $record_ids),
                    'il_dcl_field' => array('ids' => $field_ids)
                );
            case 'il_dcl_record':
                $ids = array();
                $sql = 'SELECT rf.id, d.storage_location FROM il_dcl_record_field AS rf' .
                       ' INNER JOIN il_dcl_field AS f ON (f.id = rf.field_id)' .
                       ' INNER JOIN il_dcl_datatype AS d ON (f.datatype_id = d.id) ' .
                       ' WHERE rf.record_id = ' . $this->db->quote($a_rec['id'], 'integer');
                $set = $this->db->query($sql);
                while ($rec = $this->db->fetchObject($set)) {
                    $ids[] = array('id' => $rec->id, 'record_id' => $rec->record_id, 'field_id' => $rec->field_id);
                    $this->record_field_ids_2_storage[$rec->id] = $rec->storage_location;
                }
                return array(
                    'il_dcl_record_field' => array('ids' => $ids)
                );
            case 'il_dcl_record_field':
                $record_field_id = $a_rec['id'];
                $storage_loc = $this->record_field_ids_2_storage[$record_field_id];
                return array(
                    "il_dcl_stloc{$storage_loc}_value" => array('ids' => array($record_field_id))
                );
                break;
        }
        return false;
    }


    /**
     * Read data from DB. This should result in the
     * field structure of the version set in the constructor.
     *
     * @param string $a_entity
     * @param string $a_version
     * @param array $a_ids one or multiple ids
     */
    public function readData($a_entity, $a_version, $a_ids)
    {
        $this->data = array();
        if (!is_array($a_ids)) {
            $a_ids = array($a_ids);
        }
        switch ($a_entity) {
            case 'dcl':
                $this->readDcl($a_ids);
                break;
            case 'il_dcl_table':
                $this->readDclTable($a_ids);
                break;
            case 'il_dcl_record':
                $this->readDclRecord($a_ids);
                break;
            case 'il_dcl_field':
                $this->readDclField($a_ids);
                break;
            case 'il_dcl_record_field':
                $this->readDclRecordField($a_ids);
                break;
            case 'il_dcl_stloc1_value':
            case 'il_dcl_stloc2_value':
            case 'il_dcl_stloc3_value':
                $this->readDclRecordFieldValues($a_ids, $a_entity);
                break;
        }
    }

    protected function readDclRecordFieldValues($a_ids, $a_entity)
    {
        foreach ($a_ids as $id) {
            $set = $this->db->query("SELECT * FROM {$a_entity} WHERE record_field_id = " . $this->db->quote($id, 'integer'));
            while ($rec = $this->db->fetchObject($set)) {
                $this->data[] = array(
                    'id' => $rec->id,
                    'record_field_id' => $id,
                    'value' => $rec->value,
                );
            }
        }
    }

    protected function readDclRecordField(array $a_ids)
    {
        foreach ($a_ids as $record_field_data) {
            $this->data[] = array(
                'id' => $record_field_data['id'],
                'record_id' => $record_field_data['record_id'],
                'field_id' => $record_field_data['field_id'],
            );
        }
    }


    protected function readDclRecord(array $a_ids)
    {
        foreach ($a_ids as $record_id) {
            /** @var ilDataCollectionRecord $record */
            $record = ilDataCollectionCache::getRecordCache($record_id);
            if ($record) {
                $this->data[] = array(
                    'id' => $record_id,
                    'table_id' => $record->getTableId(),
                );
            }
        }
    }


    protected function readDclField(array $a_ids)
    {
        foreach ($a_ids as $field_id) {
            /** @var ilDataCollectionField $field */
            $field = ilDataCollectionCache::getFieldCache($field_id);
            if ($field) {
                $this->data[] = array(
                    'id' => $field_id,
                    'table_id' => $field->getTableId(),
                    'title' => $field->getTitle(),
                    'description' => $field->getDescription(),
                    'datatype_id' => $field->getDatatypeId(),
                    'required' => $field->getRequired(),
                    'is_unique' => $field->isUnique(),
                    'is_locked' => $field->getLocked(),
                );
            }
        }
    }



    protected function readDclTable(array $a_ids)
    {
        foreach ($a_ids as $table_id) {
            /** @var ilDataCollectionTable $table */
            $table = ilDataCollectionCache::getTableCache($table_id);
            if ($table) {
                $this->data[] = array(
                    'id' => $table_id,
                    'obj_id' => $table->getObjId(),
                    'title' => $table->getTitle(),
                    'add_perm' => $table->getAddPerm(),
                    'edit_perm' => $table->getEditPerm(),
                    'delete_perm' => $table->getDeletePerm(),
                    'edit_by_owner' => $table->getEditByOwner(),
                    'limited' => $table->getLimited(),
                    'limit_start' => $table->getLimitStart(),
                    'limit_end' => $table->getLimitEnd(),
                    'is_visible' => $table->getIsVisible(),
                    'export_enabled' => $table->getExportEnabled(),
                    'default_sort_field_id' => $table->getDefaultSortField(),
                    'default_sort_field_order' => $table->getDefaultSortFieldOrder(),
                    'description' => $table->getDescription(),
                    'public_comments' => $table->getPublicCommentsEnabled(),
                    'view_own_records_perm' => $table->getViewOwnRecordsPerm(),
                );
            }
        }
    }

    /**
     * @param array $a_ids
     */
    protected function readDcl(array $a_ids)
    {
        foreach ($a_ids as $dcl_id) {
            if (ilObject::_lookupType($dcl_id) == 'dcl') {
                $obj = new ilObjDataCollection($dcl_id, false);
                $this->data[] = array (
                    'id' => $dcl_id,
                    'title' => $obj->getTitle(),
                    'description' => $obj->getDescription(),
                    'main_table_id' => $obj->getMainTableId(),
                    'is_online' => $obj->getOnline(),
                    'rating' => $obj->getRating(),
                    'public_notes' => $obj->getPublicNotes(),
                    'approval' => $obj->getApproval(),
                    'notification' => $obj->getNotification(),
                );
            }
        }
    }

}