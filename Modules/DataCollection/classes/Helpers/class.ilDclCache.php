<?php
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
 * Class ilDclCache
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
    protected static array $tables_cache = [];
    /**
     * @var ilDclBaseFieldModel[]
     */
    protected static array $fields_cache = [];
    /**
     * @var ilDclBaseRecordModel[]
     */
    protected static array $records_cache = [];
    /**
     * record_field_cache[record_id][field_id]
     * @var ilDclBaseRecordFieldModel[][]
     */
    protected static array $record_field_cache = [];
    /**
     * @var ilDclBaseFieldRepresentation[]
     */
    protected static array $field_representation_cache = [];
    /**
     * @var ilDclBaseRecordRepresentation[]
     */
    protected static array $record_representation_cache = [];
    /**
     * @var ilDclFieldProperty[]
     */
    protected static array $field_properties_cache = [];
    /**
     * @var ilDclDatatype[]
     */
    protected static array $datatype_cache = [];
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
     * @var array[]
     */
    protected static array $clone_mapping = [];

    public static function setCloneOf(int $old, int $new, string $type) : void
    {
        if (!self::$clone_mapping) {
            self::initCloneMapping();
        }
        self::$clone_mapping[$type][$old] = $new;
    }

    protected static function initCloneMapping() : void
    {
        self::$clone_mapping = array(
            self::TYPE_DATACOLLECTION => array(),
            self::TYPE_TABLE => array(),
            self::TYPE_FIELD => array(),
            self::TYPE_RECORD => array(),
            self::TYPE_TABLEVIEW => array(),
        );
    }

    public static function getCloneOf(int $id, string $type) : ?object
    {
        $type_cache = self::$clone_mapping[$type];
        if (!is_array($type_cache)) {
            return null;
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
            return null;
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

        return null;
    }

    public static function getTableCache(int $table_id = null) : ilDclTable
    {
        if (is_null($table_id) === true || $table_id === 0) {
            return new ilDclTable();
        }
        $tables_cache = &self::$tables_cache;
        if (!isset($tables_cache[$table_id])) {
            $tables_cache[$table_id] = new ilDclTable($table_id);
        }

        return $tables_cache[$table_id];
    }

    public static function getFieldCache(int $field_id = 0) : ilDclBaseFieldModel
    {
        $fields_cache = &self::$fields_cache;
        if (!isset($fields_cache[$field_id])) {
            $fields_cache[$field_id] = ilDclFieldFactory::getFieldModelInstance($field_id);
        }

        return $fields_cache[$field_id];
    }

    public static function getRecordCache(int $record_id = 0) : ilDclBaseRecordModel
    {
        $records_cache = &self::$records_cache;
        if (!isset($records_cache[$record_id])) {
            $records_cache[$record_id] = ilDclFieldFactory::getRecordModelInstance($record_id);
        }

        return $records_cache[$record_id];
    }

    public static function getRecordFieldCache(
        object $record, //object|ilDclBaseRecordModel
        object $field //object|ilDclBaseFieldModel
    ) : ilDclBaseRecordFieldModel
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
     * @throws ilDclException
     */
    public static function getFieldRepresentation(ilDclBaseFieldModel $field) : ilDclBaseFieldRepresentation
    {
        if (!isset(self::$field_representation_cache[$field->getId()])) {
            self::$field_representation_cache[$field->getId()] = ilDclFieldFactory::getFieldRepresentationInstance($field);
        }

        return self::$field_representation_cache[$field->getId()];
    }

    /**
     * Returns a record representation
     * @throws ilDclException
     */
    public static function getRecordRepresentation(ilDclBaseRecordFieldModel $record_field
    ) : ilDclBaseRecordRepresentation {
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
     * @param int|string $field_id
     * @return ilDclFieldProperty[]
     */
    public static function getFieldProperties($field_id) : array
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
     * @param ilDclBaseFieldModel[] $fields
     */
    public static function preloadFieldProperties(array $fields) : void
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
     * @throws ilDclException
     */
    public static function getDatatype(int $datatyp_id) : ilDclDatatype
    {
        if (self::$datatype_cache == null) {
            self::$datatype_cache = ilDclDatatype::getAllDatatype();
        }

        if (!isset(self::$datatype_cache[$datatyp_id])) {
            return new ilDclDatatype();
        }

        return self::$datatype_cache[$datatyp_id];
    }

    public static function buildFieldFromRecord(array $rec) : ilDclBaseFieldModel
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
        $field->setUnique($rec["is_unique"]);
        $fields_cache[$rec["id"]] = $field;

        return $field;
    }

    /**
     * Resets all the cache fields
     */
    public static function resetCache() : void
    {
        self::$fields_cache = array();
        self::$record_field_cache = array();
        self::$records_cache = array();
    }
}
