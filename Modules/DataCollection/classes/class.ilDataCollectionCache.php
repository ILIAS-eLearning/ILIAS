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
     * record_field_cache[record_id][field_id]
     * @var ilDataCollectionRecordField[][]
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

    /**
     * @param $field ilDataCollectionField
     * @param $record ilDataCollectionRecord
     * @return ilDataCollectionRecordField
     */
    public static function getRecordFieldCache($record, $field){
        $fid = $field->getId();
        $rid = $record->getId();
        if(!isset(self::$record_field_cache[$rid])){
            self::$record_field_cache[$rid] = array();
            self::$record_field_cache[$rid][$fid] = self::getInstance($record, $field);
        }elseif(!isset(self::$record_field_cache[$rid][$fid])){
            self::$record_field_cache[$rid][$fid] = self::getInstance($record, $field);
        }
        return self::$record_field_cache[$rid][$fid];
    }

    /**
     * This function is used to decide which type of record field is to be instanciated.
     * @param $record ilDataCollectionRecord
     * @param $field ilDataCollectionField
     * @return ilDataCollectionRecordField
     */
    public static function getInstance($record, $field){
        switch($field->getDatatypeId()){
            case ilDataCollectionDatatype::INPUTFORMAT_RATING:
                return new ilDataCollectionRatingField($record, $field);
            case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
                return new ilDataCollectionILIASRefField($record, $field);
            case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
                return new ilDataCollectionReferenceField($record, $field);
            default:
                return new ilDataCollectionRecordField($record, $field);
        }
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