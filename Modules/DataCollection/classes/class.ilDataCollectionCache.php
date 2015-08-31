<?php
require_once('class.ilDataCollectionReferenceField.php');
require_once('class.ilDataCollectionRatingField.php');
require_once('class.ilDataCollectionILIASRefField.php');
require_once('./Modules/DataCollection/classes/Field/Formula/class.ilDataCollectionFormulaField.php');
require_once('class.ilDataCollectionNReferenceField.php');

/**
 * Class ilDataCollectionCache
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionCache {

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
	 *
	 * @var ilDataCollectionRecordField[][]
	 */
	protected static $record_field_cache;


	/**
	 * @param int $table_id
	 *
	 * @return ilDataCollectionTable
	 */
	public static function getTableCache($table_id = 0) {
		if ($table_id == 0) {
			return new ilDataCollectionTable();
		}
		$tables_cache = &self::$tables_cache;
		if (!isset($tables_cache[$table_id])) {
			$tables_cache[$table_id] = new ilDataCollectionTable($table_id);
		}

		return $tables_cache[$table_id];
	}


	/**
	 * @param int $field_id
	 *
	 * @return ilDataCollectionField
	 */
	public static function getFieldCache($field_id = 0) {
		$fields_cache = &self::$fields_cache;
		if (!isset($fields_cache[$field_id])) {
			$fields_cache[$field_id] = new ilDataCollectionField($field_id);
		}

		return $fields_cache[$field_id];
	}


	/**
	 * @param int $record_id
	 *
	 * @return ilDataCollectionRecord
	 */
	public static function getRecordCache($record_id = 0) {
		$records_cache = &self::$records_cache;
		if (!isset($records_cache[$record_id])) {
			$records_cache[$record_id] = new ilDataCollectionRecord($record_id);
		}

		return $records_cache[$record_id];
	}


	/**
	 * @param $field  ilDataCollectionField
	 * @param $record ilDataCollectionRecord
	 *
	 * @return ilDataCollectionRecordField
	 */
	public static function getRecordFieldCache($record, $field) {
		$fid = $field->getId();
		$rid = $record->getId();
		if (!isset(self::$record_field_cache[$rid])) {
			self::$record_field_cache[$rid] = array();
			self::$record_field_cache[$rid][$fid] = self::getInstance($record, $field);
		} elseif (!isset(self::$record_field_cache[$rid][$fid])) {
			self::$record_field_cache[$rid][$fid] = self::getInstance($record, $field);
		}

		return self::$record_field_cache[$rid][$fid];
	}


	/**
	 * @description This function is used to decide which type of record field is to be instanciated.
	 *
	 * @param $record ilDataCollectionRecord
	 * @param $field  ilDataCollectionField
	 *
	 * @return ilDataCollectionRecordField
	 */
	public static function getInstance($record, $field) {
		switch ($field->getDatatypeId()) {
			case ilDataCollectionDatatype::INPUTFORMAT_RATING:
				return new ilDataCollectionRatingField($record, $field);
			case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
				return new ilDataCollectionILIASRefField($record, $field);
			case ilDataCollectionDatatype::INPUTFORMAT_REFERENCE:
				if (!$field->isNRef()) {
					return new ilDataCollectionReferenceField($record, $field);
				} else {
					return new ilDataCollectionNReferenceField($record, $field);
				}
			case ilDataCollectionDatatype::INPUTFORMAT_FORMULA:
				return new ilDataCollectionFormulaField($record, $field);
			default:
				return new ilDataCollectionRecordField($record, $field);
		}
	}


	/**
	 * @param $rec
	 *
	 * @return ilDataCollectionField
	 */
	public static function buildFieldFromRecord($rec) {
		$fields_cache = &self::$fields_cache;
		if (isset($fields_cache[$rec["id"]])) {
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