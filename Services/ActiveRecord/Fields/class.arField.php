<?php

/**
 * Class arField
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arField {

	const FIELD_TYPE_TEXT = 'text'; // MySQL varchar, char
	const FIELD_TYPE_INTEGER = 'integer'; // MySQL tinyint, smallint, mediumint, int, bigint
	const FIELD_TYPE_FLOAT = 'float'; // MySQL double
	const FIELD_TYPE_DATE = 'date'; // MySQL date
	const FIELD_TYPE_TIME = 'time'; // MySQL time
	const FIELD_TYPE_TIMESTAMP = 'timestamp'; // MySQL datetime
	const FIELD_TYPE_CLOB = 'clob'; // MySQL longtext
	/**
	 * @var array
	 */
	protected static $allowed_attributes = array(
		self::FIELD_TYPE_TEXT => array(
			arFieldList::LENGTH,
			arFieldList::IS_NOTNULL,
			arFieldList::IS_PRIMARY,
			arFieldList::IS_UNIQUE,
		),
		self::FIELD_TYPE_INTEGER => array(
			arFieldList::LENGTH,
			arFieldList::IS_NOTNULL,
			arFieldList::IS_PRIMARY,
			arFieldList::IS_UNIQUE,
			arFieldList::SEQUENCE,
		),
		self::FIELD_TYPE_FLOAT => array(
			arFieldList::IS_NOTNULL,
		),
		self::FIELD_TYPE_DATE => array(
			arFieldList::IS_NOTNULL,
		),
		self::FIELD_TYPE_TIME => array(
			arFieldList::IS_NOTNULL,
		),
		self::FIELD_TYPE_TIMESTAMP => array(
			arFieldList::IS_NOTNULL,
		),
		self::FIELD_TYPE_CLOB => array(
			arFieldList::IS_NOTNULL,
		),
	);
	/**
	 * @var array
	 */
	protected static $date_fields = array(
		self::FIELD_TYPE_DATE,
		self::FIELD_TYPE_TIME,
		self::FIELD_TYPE_TIMESTAMP
	);


	/**
	 * @param       $name
	 * @param array $array
	 */
	public function loadFromArray($name, array $array) {
		$this->setName($name);
		foreach ($array as $key => $value) {
			$this->{$key} = $value;
		}
	}


	/**
	 * @param          $name
	 * @param stdClass $stdClass
	 */
	public function loadFromStdClass($name, stdClass $stdClass) {
		$array = (array)$stdClass;
		$this->loadFromArray($name, $array);
	}


	/**
	 * @return array
	 */
	public function getAttributesForConnector() {
		$return = array();
		foreach (arFieldList::getAllowedConnectorFields() as $field_name) {
			if (isset($this->{$field_name}) && $this->{$field_name} AND self::isAllowedAttribute($this->getFieldType(), $field_name)) {
				$return[arFieldList::mapKey($field_name)] = $this->{$field_name};
			}
		}

		return $return;
	}


	/**
	 * @return array
	 */
	public function getAttributesForDescription() {
		$return = array();
		foreach (arFieldList::getAllowedDescriptionFields() as $field_name) {
			if ($this->{$field_name} AND self::isAllowedAttribute($this->getFieldType(), $field_name)) {
				$return[arFieldList::mapKey($field_name)] = $this->{$field_name};
			}
		}

		return $return;
	}


	/**
	 * @return bool
	 */
	public function isDateField() {
		return self::isDateFieldType($this->getFieldType());
	}


	/**
	 * @var
	 */
	protected $fieldtype;
	/**
	 * @var int
	 */
	protected $length = NULL;
	/**
	 * @var bool
	 */
	protected $is_primary = false;
	/**
	 * @var string
	 */
	protected $name = '';
	/**
	 * @var bool
	 */
	protected $not_null = false;
	/**
	 * @var bool
	 */
	protected $unique = false;
	/**
	 * @var bool
	 */
	protected $has_field = false;
	/**
	 * @var bool
	 */
	protected $sequence = false;
	/**
	 * @var bool
	 */
	protected $index = false;


	/**
	 * @param mixed $field_type
	 */
	public function setFieldType($field_type) {
		$this->fieldtype = $field_type;
	}


	/**
	 * @return mixed
	 */
	public function getFieldType() {
		return $this->fieldtype;
	}


	/**
	 * @param boolean $has_field
	 */
	public function setHasField($has_field) {
		$this->has_field = $has_field;
	}


	/**
	 * @return boolean
	 */
	public function getHasField() {
		return $this->has_field;
	}


	/**
	 * @param int $length
	 */
	public function setLength($length) {
		$this->length = $length;
	}


	/**
	 * @return int
	 */
	public function getLength() {
		return $this->length;
	}


	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}


	/**
	 * @param boolean $not_null
	 */
	public function setNotNull($not_null) {
		$this->not_null = $not_null;
	}


	/**
	 * @return boolean
	 */
	public function getNotNull() {
		return $this->not_null;
	}


	/**
	 * @param boolean $primary
	 */
	public function setPrimary($primary) {
		$this->is_primary = $primary;
	}


	/**
	 * @return boolean
	 */
	public function getPrimary() {
		return $this->is_primary;
	}


	/**
	 * @param boolean $unique
	 */
	public function setUnique($unique) {
		$this->unique = $unique;
	}


	/**
	 * @return boolean
	 */
	public function getUnique() {
		return $this->unique;
	}


	/**
	 * @param boolean $sequence
	 */
	public function setSequence($sequence) {
		$this->sequence = $sequence;
	}


	/**
	 * @return boolean
	 */
	public function getSequence() {
		return $this->sequence;
	}


	/**
	 * @param boolean $index
	 */
	public function setIndex($index) {
		$this->index = $index;
	}


	/**
	 * @return boolean
	 */
	public function getIndex() {
		return $this->index;
	}


	/**
	 * @param $type
	 * @param $field_name
	 *
	 * @return bool
	 */
	public static function isAllowedAttribute($type, $field_name) {
		if ($field_name == arFieldList::FIELDTYPE OR $field_name == arFieldList::HAS_FIELD) {
			return true;
		}

		return in_array($field_name, self::$allowed_attributes[$type]);
	}


	/**
	 * @param $field_type
	 *
	 * @return bool
	 */
	public static function isDateFieldType($field_type) {
		return in_array($field_type, self::$date_fields);
	}
}

?>
