<?php
require_once('./class.ActiveRecord.php');
require_once('./Connector/class.arConnectorPdoDB.php');

/**
 * Class arUnitTestRecord
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.5
 */
class arUnitTestRecord extends ActiveRecord {

	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 * @deprecated
	 */
	static function returnDbTableName() {
		return 'ar_demo_real_record';
	}


	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return 'ar_demo_real_record';
	}


	/**
	 * @param int $primary
	 */
	public function __construct($primary = 0) {
		parent::__construct($primary, new arConnectorPdoDB());
	}


	/**
	 * @var int
	 *
	 * @db_has_field        true
	 * @db_is_unique        true
	 * @db_is_primary       true
	 * @db_is_notnull       true
	 * @db_fieldtype        integer
	 * @db_length           4
	 */
	protected $id = 0;
	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           200
	 * @con_index           true
	 */
	protected $title = '';
	/**
	 * @var string
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           200
	 */
	public $description = '';
	/**
	 * @var array
	 * @db_has_field        true
	 * @db_fieldtype        text
	 * @db_length           200
	 */
	protected $usr_ids = array();


	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}


	/**
	 * @param array $usr_ids
	 */
	public function setUsrIds($usr_ids) {
		$this->usr_ids = $usr_ids;
	}


	/**
	 * @return array
	 */
	public function getUsrIds() {
		return $this->usr_ids;
	}


	/**
	 * @param $field_name
	 *
	 * @return mixed|string
	 */
	public function sleep($field_name) {
		switch ($field_name) {
			case 'usr_ids':
				return json_encode($this->getUsrIds());
		}
		parent::sleep($field_name);
	}


	/**
	 * @param $field_name
	 * @param $field_value
	 *
	 * @return mixed
	 */
	public function wakeUp($field_name, $field_value) {
		switch ($field_name) {
			case 'usr_ids':
				return json_decode($field_value);
		}
		parent::wakeUp($field_name, $field_value);
	}


	/**
	 * @param string $field_name
	 *
	 * @return mixed|string
	 */
	protected function serializeToCSV($field_name) {
		return $this->sleep($field_name);
	}
}

?>
