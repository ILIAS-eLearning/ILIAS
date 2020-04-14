<?php

/**
 * Class ilDclCache
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclCache
{
    const TYPE_DATACOLLECTION = 'dcl';
    const TYPE_TABLE = 'table';
    const TYPE_FIELD = 'field';
    const TYPE_RECORD = 'record';
    const TYPE_TABLEVIEW = 'tableview';
    /**
     * @var ilDclTable[]
     */
    protected static $tables_cache;
    /**
     * @var ilDclBaseFieldModel[]
     */
    protected static $fields_cache;
    /**
     * @var ilDclBaseRecordModel[]
     */
    protected static $records_cache;
    /**
     * record_field_cache[record_id][field_id]
     *
     * @var ilDclBaseRecordFieldModel[][]
     */
    protected static $record_field_cache;
    /**
     * @var ilDclFieldRepresentation[]
     */
    protected static $field_representation_cache;
    /**
     * @var ilDclRecordRepresentation[]
     */
    protected static $record_representation_cache;
    /**
     * @var ilDclFieldProperty[]
     */
    protected static $field_properties_cache;
    /**
     * @var ilDclDatatype
     */
    protected static $datatype_cache;
    /**
     * used when cloning datacollections, contains mappings of all components
     * form:
     * array(
     *      'dcl' => ($old_id1 => $new_id1, ...),
     *      'table' => ($old_id1 => $new_id1, ...),
     *      'field' => " "
     *      'record' => " "
     *      'tableview' => " "
     * )
     *
     * @var array[]
     */
    protected static $clone_mapping;


    public static function setCloneOf($old, $new, $type)
    {
        if (!self::$clone_mapping) {
            self::initCloneMapping();
        }
        self::$clone_mapping[$type][$old] = $new;
    }


    protected static function initCloneMapping()
    {
        self::$clone_mapping = array(
            self::TYPE_DATACOLLECTION => array(),
            self::TYPE_TABLE => array(),
            self::TYPE_FIELD => array(),
            self::TYPE_RECORD => array(),
            self::TYPE_TABLEVIEW => array(),
        );
    }


    public static function getCloneOf($id, $type)
    {
        $type_cache = self::$clone_mapping[$type];
        if (!is_array($type_cache)) {
            return false;
        }

        if (isset($type_cache[$id])) {
            $clone_id = $type_cache[$id];
        } else {
            foreach ($type_cache as $key => $mapping) {
                if ($mapping == $id) {
                    $clone_id = $key;
                }
            }
        }

        if (!$clone_id) {
            return false;
        }

        switch ($type) {
            case self::TYPE_DATACOLLECTION:
                return new ilObjDataCollection($clone_id);
            case self::TYPE_FIELD:
                return self::getFieldCache($clone_id);
            case self::TYPE_TABLE:
                return self::getTableCache($clone_id);
            case self::TYPE_RECORD:
                return self::getRecordCache($clone_id);
        }
    }


    /**
     * @param int $table_id
     *
     * @return ilDclTable
     */
    public static function getTableCache($table_id = 0)
    {
        if ($table_id == 0) {
            return new ilDclTable();
        }
        $tables_cache = &self::$tables_cache;
        if (!isset($tables_cache[$table_id])) {
            $tables_cache[$table_id] = new ilDclTable($table_id);
        }

        return $tables_cache[$table_id];
    }


    /**
     * @param int $field_id
     *
     * @return ilDclBaseFieldModel
     */
    public static function getFieldCache($field_id = 0)
    {
        $fields_cache = &self::$fields_cache;
        if (!isset($fields_cache[$field_id])) {
            $fields_cache[$field_id] = ilDclFieldFactory::getFieldModelInstance($field_id);
        }

        return $fields_cache[$field_id];
    }


    /**
     * @param int $record_id
     *
     * @return ilDclBaseRecordModel
     */
    public static function getRecordCache($record_id = 0)
    {
        $records_cache = &self::$records_cache;
        if (!isset($records_cache[$record_id])) {
            $records_cache[$record_id] = ilDclFieldFactory::getRecordModelInstance($record_id);
        }

        return $records_cache[$record_id];
    }


    /**
     * @param $field  ilDclBaseFieldModel
     * @param $record ilDclBaseRecordModel
     *
     * @return ilDclBaseRecordFieldModel
     */
    public static function getRecordFieldCache($record, $field)
    {
        $fid = $field->getId();
        $rid = $record->getId();
        if (!isset(self::$record_field_cache[$rid])) {
            self::$record_field_cache[$rid] = array();
            self::$record_field_cache[$rid][$fid] = ilDclFieldFactory::getRecordFieldInstance($field, $record);
        } elseif (!isset(self::$record_field_cache[$rid][$fid])) {
            self::$record_field_cache[$rid][$fid] = ilDclFieldFactory::getRecordFieldInstance($field, $record);
        }

        return self::$record_field_cache[$rid][$fid];
    }


    /**
     * @param ilDclBaseFieldModel $field
     *
     * @return ilDclBaseFieldRepresentation
     * @throws ilDclException
     */
    public static function getFieldRepresentation(ilDclBaseFieldModel $field)
    {
        if (!isset(self::$field_representation_cache[$field->getId()])) {
            self::$field_representation_cache[$field->getId()] = ilDclFieldFactory::getFieldRepresentationInstance($field);
        }

        return self::$field_representation_cache[$field->getId()];
    }


    /**
     * Returns a record representation
     *
     * @param ilDclBaseRecordFieldModel $record_field
     *
     * @return ilDclBaseRecordRepresentation
     * @throws ilDclException
     */
    public static function getRecordRepresentation(ilDclBaseRecordFieldModel $record_field)
    {
        if ($record_field == null) {
            throw new ilDclException("Cannot get Representation of null object!");
        }

        if (!isset(self::$record_representation_cache[$record_field->getId()])) {
            self::$record_representation_cache[$record_field->getId()] = ilDclFieldFactory::getRecordRepresentationInstance($record_field);
        }

        return self::$record_representation_cache[$record_field->getId()];
    }


    /**
     * Cache Field properties
     *
     * @param $field_id
     *
     * @return ilDclFieldProperty
     */
    public static function getFieldProperties($field_id)
    {
        if (!isset(self::$field_properties_cache[$field_id])) {
            self::$field_properties_cache[$field_id] = array();
            $result = ilDclFieldProperty::where(array('field_id' => $field_id))->get();
            foreach ($result as $prop) {
                self::$field_properties_cache[$field_id][$prop->getName()] = $prop;
            }
        }

        return self::$field_properties_cache[$field_id];
    }


    /**
     * Preloads field properties
     *
     * @param ilDclBaseFieldModel[] $fields
     */
    public static function preloadFieldProperties(array $fields)
    {
        foreach ($fields as $field_key => $field) {
            if (isset(self::$field_properties_cache[$field->getId()])) {
                unset($fields[$field_key]);
            }
        }

        if (count($fields) > 0) {
            $field_ids = array();
            foreach ($fields as $field) {
                $field_ids[] = $field->getId();
            }
            $result = ilDclFieldProperty::where(array('field_id' => $field_ids), 'IN')->get();
            foreach ($result as $prop) {
                if (!isset(self::$field_properties_cache[$prop->getFieldId()])) {
                    self::$field_properties_cache[$prop->getFieldId()] = array();
                }
                self::$field_properties_cache[$prop->getFieldId()][$prop->getName()] = $prop;
            }
        }
    }


    /**
     * Get cached datatypes
     *
     * @param $datatyp_id
     *
     * @return mixed
     * @throws ilDclException
     */
    public static function getDatatype($datatyp_id)
    {
        if (self::$datatype_cache == null) {
            self::$datatype_cache = ilDclDatatype::getAllDatatype();
        }

        if (!isset(self::$datatype_cache[$datatyp_id])) {
            return new ilDclDatatype();
        }

        return self::$datatype_cache[$datatyp_id];
    }


    /**
     * @param $rec
     *
     * @return ilDclBaseFieldModel
     */
    public static function buildFieldFromRecord($rec)
    {
        $fields_cache = &self::$fields_cache;
        if (isset($fields_cache[$rec["id"]])) {
            return $fields_cache[$rec["id"]];
        }
        $field = ilDclFieldFactory::getFieldModelInstanceByClass(new ilDclBaseFieldModel($rec['id']));
        $field->setId($rec["id"]);
        $field->setTableId($rec["table_id"]);
        $field->setTitle($rec["title"]);
        $field->setDescription($rec["description"]);
        $field->setDatatypeId($rec["datatype_id"]);
        $field->setRequired($rec["required"]);
        $field->setUnique($rec["is_unique"]);
        $field->setLocked($rec["is_locked"]);
        $fields_cache[$rec["id"]] = $field;

        return $field;
    }


    /**
     * Resets all the cache fields
     */
    public static function resetCache()
    {
        self::$fields_cache = array();
        self::$record_field_cache = array();
        self::$records_cache = array();
    }
}
