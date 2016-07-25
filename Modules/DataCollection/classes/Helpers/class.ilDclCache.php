<?php
require_once('./Modules/DataCollection/classes/Fields/Reference/class.ilDclReferenceRecordFieldModel.php');
require_once('./Modules/DataCollection/classes/Fields/Rating/class.ilDclRatingRecordFieldModel.php');
require_once('./Modules/DataCollection/classes/Fields/IliasReference/class.ilDclIliasReferenceRecordFieldModel.php');
require_once('./Modules/DataCollection/classes/Fields/Formula/class.ilDclFormulaRecordFieldModel.php');
require_once('./Modules/DataCollection/classes/Fields/Text/class.ilDclTextRecordFieldModel.php');
require_once('./Modules/DataCollection/classes/Fields/NReference/class.ilDclNReferenceRecordFieldModel.php');
require_once('./Modules/DataCollection/classes/Fields/class.ilDclFieldFactory.php');

/**
 * Class ilDclCache
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDclCache {

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
	 * @param int $table_id
	 *
	 * @return ilDclTable
	 */
	public static function getTableCache($table_id = 0) {
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
	public static function getFieldCache($field_id = 0) {
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
	public static function getRecordCache($record_id = 0) {
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
	public static function getRecordFieldCache($record, $field) {
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
	public static function getFieldRepresentation(ilDclBaseFieldModel $field) {
		if(!isset(self::$field_representation_cache[$field->getId()])) {
			self::$field_representation_cache[$field->getId()] = ilDclFieldFactory::getFieldRepresentationInstance($field);
		}
		return self::$field_representation_cache[$field->getId()];
	}


	/**
	 * Returns a record representation
	 * @param ilDclBaseRecordFieldModel $record_field
	 *
	 * @return ilDclBaseRecordRepresentation
	 * @throws ilDclException
	 */
	public static function getRecordRepresentation(ilDclBaseRecordFieldModel $record_field) {
		if($record_field == null) {
			throw new ilDclException("Cannot get Representation of null object!");
		}

		if(!isset(self::$record_representation_cache[$record_field->getId()])) {
			self::$record_representation_cache[$record_field->getId()] = ilDclFieldFactory::getRecordRepresentationInstance($record_field);
		}
		return self::$record_representation_cache[$record_field->getId()];
	}


	/**
	 * Cache Field properties
	 * @param $field_id
	 *
	 * @return ilDclFieldProperty
	 */
	public static function getFieldProperties($field_id) {
		if(!isset(self::$field_properties_cache[$field_id])) {
			self::$field_properties_cache[$field_id] = array();
			$result = ilDclFieldProperty::where(array('field_id'=>$field_id))->get();
			foreach($result as $prop) {
				self::$field_properties_cache[$field_id][$prop->getName()] = $prop;
			}
		}
		return self::$field_properties_cache[$field_id];
	}


	/**
	 * Preloads field properties
	 * @param array $field_ids
	 */
	public static function preloadFieldProperties(array $field_ids) {
		foreach($field_ids as $field_key => $field_id) {
			if(isset(self::$field_properties_cache[$field_id])) {
				unset($field_ids[$field_key]);
			}
		}

		if(count($field_ids) > 0) {
			$result = ilDclFieldProperty::where(array('field_id'=>$field_ids), 'IN')->get();
			foreach($result as $prop) {
				if(!isset(self::$field_properties_cache[$result['field_id']])) {
					self::$field_properties_cache[$result['field_id']] = array();
				}
				self::$field_properties_cache[$result['field_id']][$prop->getName()] = $prop;
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
	public static function getDatatype($datatyp_id) {
		if(self::$datatype_cache == NULL) {
			self::$datatype_cache = ilDclDatatype::getAllDatatype();
		}

		if(!isset(self::$datatype_cache[$datatyp_id])) {
			return new ilDclDatatype();
		}
		return self::$datatype_cache[$datatyp_id];
	}


	/**
	 * @param $rec
	 *
	 * @return ilDclBaseFieldModel
	 */
	public static function buildFieldFromRecord($rec) {
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
	public static function resetCache() {
		self::$fields_cache = array();
		self::$record_field_cache = array();
		self::$records_cache = array();
	}
}