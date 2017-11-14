<?php
/**
 * Class ilField
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblField extends ActiveRecord {

	const TABLE_NAME = 'il_bibl_field';
	const DATA_TYPE_RIS = 1;
	const DATA_TYPE_BIBTEX = 2;
	const FILTER_TYPE_TEXT_INPUT = 1;
	const FILTER_TYPE_SELECT_INPUT = 2;
	const FILTER_TYPE_MULTI_SELECT_INPUT = 3;


	/**
	 * @return string
	 * @deprecated
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}


	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}


	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 * @con_is_notnull true
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 */
	protected $id;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     50
	 * @con_is_notnull true
	 */
	protected $field;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 * @con_is_notnull true
	 */
	protected $data_type;
	/**
	 * @var
	 *
	 * @con_has_field true
	 * @con_fieldtype integer
	 * @con_length    1
	 */
	protected $filter_type;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     3
	 */
	protected $position;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     1
	 * @con_is_notnull true
	 */
	protected $is_standard;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 * @con_is_notnull true
	 */
	protected $object_id;


	/**
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param integer $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	public function getField() {
		return $this->field;
	}


	/**
	 * @param string $field
	 */
	public function setField($field) {
		$this->field = $field;
	}


	/**
	 * @return integer
	 */
	public function getisRisType() {
		return $this->is_ris_type;
	}


	/**
	 * @param integer $is_ris_type
	 */
	public function setIsRisType($is_ris_type) {
		$this->is_ris_type = $is_ris_type;
	}


	/**
	 * @return integer
	 */
	public function getFilterType() {
		return $this->filter_type;
	}


	/**
	 * @param integer $filter_type
	 */
	public function setFilterType($filter_type) {
		$this->filter_type = $filter_type;
	}


	/**
	 * @return integer
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param integer $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}


	/**
	 * @return integer
	 */
	public function getisStandard() {
		return $this->is_standard;
	}


	/**
	 * @param integer $is_standard
	 */
	public function setIsStandard($is_standard) {
		$this->is_standard = $is_standard;
	}


	/**
	 * @return integer
	 */
	public function getObjectId() {
		return $this->object_id;
	}


	/**
	 * @param integer $object_id
	 */
	public function setObjectId($object_id) {
		$this->object_id = $object_id;
	}


	/**
	 * @param $obj_id ILIAS-Object_ID
	 *
	 * @return array of string of all field-types in ILIAS-Object with the given obj_id
	 */
	public static function getAvailableFieldsForObjId($obj_id) {
		global $DIC;
		$sql = "SELECT DISTINCT(il_bibl_attribute.name) FROM il_bibl_data 
					JOIN il_bibl_entry ON il_bibl_entry.data_id = il_bibl_data.id
					JOIN il_bibl_attribute ON il_bibl_attribute.entry_id = il_bibl_entry.id
				WHERE il_bibl_data.id = %s;";

		$result = $DIC->database()->queryF($sql, [ 'integer' ], [ $obj_id ]);

		$data = [];
		while ($d = $DIC->database()->fetchAssoc($result)) {
			$data[] = $d['name'];
		}

		return $data;
	}

	/**
	 * @param $obj_id ILIAS-Object_ID
	 *
	 * @return array of string of all field-types in ILIAS-Object with the given obj_id
	 */
	public static function getAttributeNameAndFileName($obj_id) {
		global $DIC;
		$sql = "SELECT DISTINCT(il_bibl_attribute.name), filename FROM il_bibl_attribute
				JOIN il_bibl_entry ON il_bibl_attribute.entry_id = il_bibl_entry.id
				JOIN il_bibl_data ON il_bibl_data.id = il_bibl_entry.data_id";

		$result = $DIC->database()->queryF($sql, [ 'integer' ], [ $obj_id ]);

		$data = [];
		while ($d = $DIC->database()->fetchAssoc($result)) {
			$data[] = $d['name'];
		}
		return $data;
	}
}