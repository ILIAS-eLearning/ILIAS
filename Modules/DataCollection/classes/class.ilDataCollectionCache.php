<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * Date: 1/7/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 *
 * This will cache all instances of the DataCollection module. never use new ilDataCollectionTable only use getTableCache
 */
class ilDataCollectionCache{
    /**
     * @var ilDataCollectionTable[]
     */
    protected static $tables_cache;

    /**
     * @var ilDataCollectionField[]
     */
    protected static $fields_cache;

    /**
     * @var ilDataCollectionRecord[]
     */
    protected static $records_cache;

    /**
     * @var ilDataCollectionRecordField[]
     */
    protected static $record_field_cache;

    public static function getTableCache($table_id = 0){
        $tables_cache = &self::$tables_cache;
        if(!isset($tables_cache[$table_id]))
            $tables_cache[$table_id] = new ilDataCollectionTable($table_id);
        return $tables_cache[$table_id];
    }

    public static function getFieldCache($field_id = 0){
        $fields_cache = &self::$fields_cache;
        if(!isset($fields_cache[$field_id]))
            $fields_cache[$field_id] = new ilDataCollectionField($field_id);
        return $fields_cache[$field_id];
    }

    public static function getRecordCache($record_id = 0){
        $records_cache = &self::$records_cache;
        if(!isset($records_cache[$record_id]))
            $records_cache[$record_id] = new ilDataCollectionRecord($record_id);
        return $records_cache[$record_id];
    }

    public static function buildFieldFromRecord($rec){
        $fields_cache = &self::$fields_cache;
        if(isset($fields_cache[$rec["id"]])){
            return $fields_cache[$rec["id"]];
        }
        $field = new ilDataCollectionField();
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
}