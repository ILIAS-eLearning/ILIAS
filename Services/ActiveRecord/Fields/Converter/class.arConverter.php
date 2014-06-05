<?php
require_once(dirname(__FILE__) . '/../class.arFieldList.php');

/**
 * Class arConverter
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.4
 *
 *
 * @description         $arConverter = new arConverter('my_msql_table_name', 'arMyRecordClass');
 *                      $arConverter->readStructure();
 *                      $arConverter->downloadClassFile();
 *
 */
class arConverter {

	const REGEX = "/([a-z]*)\\(([0-9]*)\\)/us";
	/**
	 * @var array
	 */
	protected static $field_map = array(
		'varchar' => arField::FIELD_TYPE_TEXT,
		'char' => arField::FIELD_TYPE_TEXT,
		'int' => arField::FIELD_TYPE_INTEGER,
		'tinyint' => arField::FIELD_TYPE_INTEGER,
		'smallint' => arField::FIELD_TYPE_INTEGER,
		'mediumint' => arField::FIELD_TYPE_INTEGER,
		'bigint' => arField::FIELD_TYPE_INTEGER,
	);
	/**
	 * @var array
	 */
	protected static $length_map = array(
		arField::FIELD_TYPE_TEXT => false,
		arField::FIELD_TYPE_DATE => false,
		arField::FIELD_TYPE_TIME => false,
		arField::FIELD_TYPE_TIMESTAMP => false,
		arField::FIELD_TYPE_CLOB => false,
		arField::FIELD_TYPE_FLOAT => false,
		arField::FIELD_TYPE_INTEGER => array(
			11 => 4,
			4 => 1,
		)
	);
	/**
	 * @var string
	 */
	protected $table_name = '';
	/**
	 * @var string
	 */
	protected $class_name = '';
	/**
	 * @var array
	 */
	protected $structure = array();


	/**
	 * @param $table_name
	 * @param $class_name
	 */
	public function __construct($table_name, $class_name) {
		$this->setClassName($class_name);
		$this->setTableName($table_name);
		$this->readStructure();
	}


	public function readStructure() {
		$sql = 'DESCRIBE ' . $this->getTableName();
		$res = self::getDB()->query($sql);
		while ($data = self::getDB()->fetchObject($res)) {
			$this->addStructure($data);
		}
	}


	public function downloadClassFile() {
		$tpl = new ilTemplate(dirname(__FILE__) . '/templates/class.arTemplate.txt', true, true);
		$tpl->setVariable('TABLE_NAME', $this->getTableName());
		$tpl->setVariable('CLASS_NAME', $this->getClassName());

		foreach ($this->getStructure() as $str) {
			$tpl->touchBlock('member');
			$tpl->setVariable('FIELD_NAME', $str->field);
			$tpl->setVariable('DECLARATION', 'int');
			foreach ($this->returnAttributesForField($str) as $name => $value) {
				$tpl->setCurrentBlock('attribute');
				$tpl->setVariable('NAME', $name);
				$tpl->setVariable('VALUE', $value);
				$tpl->parseCurrentBlock();
			}
		}

		//		echo '<pre>' . print_r($tpl->get(), 1) . '</pre>';

		header('Content-type: application/x-httpd-php');
		header("Content-Disposition: attachment; filename=\"class." . $this->getClassName() . ".php\"");
		echo $tpl->get();
		exit;
	}


	/**
	 * @param stdClass $field
	 *
	 * @return array
	 */
	protected function returnAttributesForField(stdClass $field) {
		$attributes = array();
		$attributes[arFieldList::HAS_FIELD] = 'true';
		$attributes[arFieldList::FIELDTYPE] = self::lookupFieldType($field->type);
		$attributes[arFieldList::LENGTH] = self::lookupFieldLength($field->type);

		if ($field->null == 'NO') {
			$attributes[arFieldList::IS_NOTNULL] = 'true';
		}

		if ($field->key == 'PRI') {
			$attributes[arFieldList::IS_PRIMARY] = 'true';
			$attributes[arFieldList::IS_UNIQUE] = 'true';
		}

		return $attributes;
	}


	/**
	 * @param $string
	 *
	 * @return string
	 */
	protected static function lookupFieldType($string) {
		preg_match(self::REGEX, $string, $matches);

		return self::$field_map[$matches[1]];
	}


	/**
	 * @param $string
	 *
	 * @return string
	 */
	protected static function lookupFieldLength($string) {
		$field_type = self::lookupFieldType($string);

		preg_match(self::REGEX, $string, $matches);

		if (self::$length_map[$field_type][$matches[2]]) {
			return self::$length_map[$field_type][$matches[2]];
		} else {
			return $matches[2];
		}
	}


	/**
	 * @return ilDB
	 */
	public static function getDB() {
		global $ilDB;

		/**
		 * @var $ilDB ilDB
		 */

		return $ilDB;
	}


	/**
	 * @param string $table_name
	 */
	public function setTableName($table_name) {
		$this->table_name = $table_name;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}


	/**
	 * @param array $structure
	 */
	public function setStructure($structure) {
		$this->structure = $structure;
	}


	/**
	 * @return array
	 */
	public function getStructure() {
		return $this->structure;
	}


	/**
	 * @param stdClass $structure
	 */
	public function addStructure(stdClass $structure) {
		$this->structure[] = $structure;
	}


	/**
	 * @param string $class_name
	 */
	public function setClassName($class_name) {
		$this->class_name = $class_name;
	}


	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->class_name;
	}
}

?>
