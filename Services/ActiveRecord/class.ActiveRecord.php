<?php
require_once('class.ActiveRecordList.php');
require_once('Connector/class.arConnector.php');
require_once('Connector/class.arConnectorDB.php');

/**
 * Class ActiveRecord
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @experimental
 * @description
 *
 * @version 1.1.04
 */
abstract class ActiveRecord {

	/**
	 * @var array
	 */
	protected static $db_fields = array();
	/**
	 * @var array
	 */
	protected static $form_fields = array();
	/**
	 * @var array
	 */
	protected static $primary_fields = array();
	/**
	 * @var array
	 */
	private static $object_cache = array();
	/**
	 * @var array
	 */
	protected static $possible_fields = array(
		'db' => array(
			'db_has_field',
			'db_is_unique',
			'db_is_primary',
			'db_is_notnull',
			'db_fieldtype',
			'db_length',
		),
		'form' => array(
			'form_has_field',
			'form_type',
		),
	);
	/**
	 * @var arConnector
	 */
	protected $connector;


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 */
	abstract static function returnDbTableName();


	/**
	 * @return array
	 */
	public static function returnDbFields() {
		$class = get_called_class();
		if (! isset(self::$db_fields[$class])) {
			new $class();
		}

		return self::$db_fields[$class];
	}


	/**
	 * @return string
	 */
	public static function returnPrimaryFieldName() {
		$class = get_called_class();
		if (! isset(self::$primary_fields[$class])) {
			new $class();
		}

		return self::$primary_fields[$class]['fieldname'];
	}


	/**
	 * @return string
	 */
	public static function returnPrimaryFieldType() {
		$class = get_called_class();
		if (! isset(self::$primary_fields[$class])) {
			new $class();
		}

		return self::$primary_fields[$class]['fieldtype'];
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
	// protected $id = 0;
	/**
	 * @param int         $id
	 * @param arConnector $connector
	 */
	public function __construct($id = 0, arConnector $connector = NULL) {
		if ($connector == NULL) {
			$this->connector = new arConnectorDB();
		}
		self::setDBFields($this);
		//		self::setFormFields($this);
		if (self::returnPrimaryFieldName() === 'id') {
			$this->id = $id;
		} else {
			$key = self::returnPrimaryFieldName();
			$this->{$key} = $id;
		}
		if ($id !== 0 AND $id !== NULL AND $id !== false) {
			$this->read();
		}
	}


	/**
	 * @return array
	 */
	public function __asArray() {
		$return = array();
		foreach (array_keys(self::returnDbFields()) as $fieldname) {
			$return[$fieldname] = $this->{$fieldname};
		}

		return $return;
	}


	//
	// Database
	//
	/**
	 * @param $field_name
	 *
	 * @return mixed
	 */
	public function sleep($field_name) {
		return NULL;
	}


	/**
	 * @param $field_name
	 * @param $field_value
	 *
	 * @return mixed
	 */
	public function wakeUp($field_name, $field_value) {
		return NULL;
	}


	/**
	 * @return array
	 */
	final public function getArrayForDb() {
		$data = array();
		foreach (self::returnDbFields() as $field_name => $field_info) {
			if ($this->sleep($field_name) === NULL) {
				$data[$field_name] = array( $field_info->db_type, $this->$field_name );
			} else {
				$data[$field_name] = array( $field_info->db_type, $this->sleep($field_name) );
			}
		}

		return $data;
	}


	/**
	 * @return ActiveRecord
	 *
	 * @description Returns an instance of the instatiated calling active record (needs to be done in static methods)
	 * @TODO        : This should be cached somehow
	 */
	static protected function getCalledClass() {
		$class = get_called_class();

		/**
		 * @var $model ActiveRecord
		 */

		return new $class();
	}


	/**
	 * @return bool
	 */
	final public static function installDB() {
		return self::getCalledClass()->installDatabase();
	}


	/**
	 * @param $old_name
	 * @param $new_name
	 *
	 * @return bool
	 */
	final public static function renameDBField($old_name, $new_name) {
		return self::getCalledClass()->connector->renameField(self::getCalledClass(), $old_name, $new_name);
	}


	/**
	 * @return bool
	 */
	final public static function tableExists() {
		return self::getCalledClass()->connector->checkTableExists(self::getCalledClass());
	}


	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	final public static function fieldExists($field_name) {
		return self::getCalledClass()->connector->checkFieldExists(self::getCalledClass(), $field_name);
	}


	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	final public static function removeDBField($field_name) {
		return self::getCalledClass()->connector->removeField(self::getCalledClass(), $field_name);
	}


	/**
	 * @return bool
	 */
	final protected function installDatabase() {
		if (! $this->tableExists()) {
			$fields = array();
			foreach (self::returnDbFields() as $field_name => $field_infos) {
				$fields[$field_name] = $this->getDBAttributesOfField($field_infos);
			}

			return $this->connector->installDatabase($this, $fields);
		} else {
			return $this->connector->updateDatabase($this);
		}
	}


	/**
	 * @return bool
	 */
	final public static function updateDB() {
		if (! self::tableExists()) {
			self::getCalledClass()->installDatabase();

			return true;
		}

		return self::getCalledClass()->connector->updateDatabase(self::getCalledClass());
	}


	/**
	 * @param $field
	 *
	 * @return array
	 */
	public function getDBAttributesOfField($field) {
		$attributes = array();
		$attributes['type'] = $field->db_type;
		if ($field->length) {
			$attributes['length'] = $field->length;
		}
		if ($field->notnull) {
			$attributes['notnull'] = $field->notnull;
		}

		return $attributes;
	}


	/**
	 * @return bool
	 */
	final public static function resetDB() {
		return self::getCalledClass()->connector->resetDatabase(self::getCalledClass());
	}


	/**
	 * @return bool
	 */
	final public static function truncateDB() {
		return self::getCalledClass()->connector->truncateDatabase(self::getCalledClass());
	}


	/**
	 * @return bool
	 */
	final public static function flushDB() {
		return self::truncateDB();
	}

	//
	// CRUD
	//
	public function saveDeprecated() {
		if ($this->getId() === 0) {
			$this->create();
		} else {
			$this->update();
		}
	}


	public function create() {
		$class = get_class($this);
		// TODO evtl. check field length etc.
		try {
			if (self::returnPrimaryFieldName() === 'id') {
				$this->setId($this->connector->nextID($this));
				self::$object_cache[$class][$this->getId()] = $this;
			}
			$this->connector->create($this, $this->getArrayForDb());
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}


	public function afterObjectLoad() {
	}


	public function read() {
		$class = get_class($this);
		foreach ($this->connector->read($this) as $rec) {
			foreach ($this->getArrayForDb() as $k => $v) {
				if ($this->wakeUp($k, $rec->{$k}) === NULL) {
					$this->{$k} = $rec->{$k};
				} else {
					$this->{$k} = $this->wakeUp($k, $rec->{$k});
				}
			}
			self::$object_cache[$class][$this->getPrimaryFieldValue()] = $this;
			self::$object_cache[$class][$this->getPrimaryFieldValue()]->afterObjectLoad();
		}
	}


	public function update() {
		$class = get_class($this);
		$this->connector->update($this);
		self::$object_cache[$class][$this->getPrimaryFieldValue()] = $this;
	}


	public function delete() {
		$class = get_class($this);
		$this->connector->delete($this);
		unset(self::$object_cache[$class][$this->getPrimaryFieldValue()]);
	}


	/**
	 * @param string $str
	 * @param bool   $capitalise_first_char
	 *
	 * @return string
	 */
	public static function _toCamelCase($str, $capitalise_first_char = false) {
		if ($capitalise_first_char) {
			$str[0] = strtoupper($str[0]);
		}
		$func = create_function('$c', 'return strtoupper($c[1]);');

		return preg_replace_callback('/_([a-z])/', $func, $str);
	}


	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return array
	 */
	public function __call($name, $arguments) {
		// Getter
		if (preg_match("/get([a-zA-Z]*)/u", $name, $matches) AND count($arguments) == 0) {
			return $this->{self::fromCamelCase($matches[1])};
		}
		// Setter
		if (preg_match("/set([a-zA-Z]*)/u", $name, $matches) AND count($arguments) == 1) {
			$this->{self::fromCamelCase($matches[1])} = $arguments[0];
		}
		if (preg_match("/findBy([a-zA-Z]*)/u", $name, $matches) AND count($arguments) == 1) {
			return self::where(array( self::fromCamelCase($matches[1]) => $arguments[0] ))->get();
		}
	}


	/**
	 * @param $id
	 *
	 * @return ActiveRecord
	 */
	public static function find($id) {
		$class = get_called_class();
		/**
		 * @var $obj ActiveRecord
		 */
		if (! self::$object_cache[$class][$id]) {
			$obj = new $class($id);
			$obj->loadObject($id);
		}

		return self::$object_cache[$class][$id];
	}


	/**
	 * @param      $where
	 * @param null $operator
	 *
	 * @return ActiveRecordList
	 */
	public static function where($where, $operator = NULL) {
		$srModelObjectList = new ActiveRecordList(get_called_class());
		$srModelObjectList->where($where, $operator);

		return $srModelObjectList;
	}


	/**
	 * @param ActiveRecord $ar
	 * @param array        $on
	 *
	 * @return $this
	 */
	public static function join(ActiveRecord $ar, $on = array()) {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->join($ar, $on);
	}


	/**
	 * @param        $orderBy
	 * @param string $orderDirection
	 *
	 * @return ActiveRecordList
	 */
	public static function orderBy($orderBy, $orderDirection = 'ASC') {
		$srModelObjectList = new ActiveRecordList(get_called_class());
		$srModelObjectList->orderBy($orderBy, $orderDirection);

		return $srModelObjectList;
	}


	/**
	 * @param $start
	 * @param $end
	 *
	 * @return ActiveRecordList
	 */
	public static function limit($start, $end) {
		$srModelObjectList = new ActiveRecordList(get_called_class());
		$srModelObjectList->limit($start, $end);

		return $srModelObjectList;
	}


	/**
	 * @return int
	 */
	public static function affectedRows() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->affectedRows();
	}


	/**
	 * @return int
	 */
	public static function count() {
		return self::affectedRows();
	}


	/**
	 * @return array
	 */
	public static function get() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->get();
	}


	/**
	 * @return ActiveRecordList
	 */
	public static function debug() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->debug();
	}


	/**
	 * @return mixed
	 */
	public static function first() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->first();
	}


	/**
	 * @return ActiveRecordList
	 */
	public static function getCollection() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList;
	}


	/**
	 * @return mixed
	 */
	public static function last() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->last();
	}


	/**
	 * @return mixed
	 */
	public static function getFirstFromLastQuery() {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->getFirstFromLastQuery();
	}


	/**
	 * @param null $key
	 * @param null $values
	 *
	 * @return array
	 */
	public static function getArray($key = NULL, $values = NULL) {
		$srModelObjectList = new ActiveRecordList(get_called_class());

		return $srModelObjectList->getArray($key, $values);
	}


	/**
	 * @param $id
	 */
	public function loadObject($id) {
		$class = get_class($this);
		if (! self::$object_cache[$class][$id]) {
			self::$object_cache[$class][$id] = new $class($id);
		}
	}


	/**
	 * @param array $array
	 *
	 * @return $this
	 */
	public function buildFromArray(array $array) {
		$class = get_class($this);
		$primary = self::returnPrimaryFieldName();
		$primary_value = $array[$primary];
		if ($primary_value AND self::$object_cache[$class][$primary_value]) {
			return self::$object_cache[$class][$primary_value];
		}
		foreach ($array as $field_name => $value) {
			if ($this->wakeUp($field_name, $value) === NULL) {
				$this->{$field_name} = $value;
			} else {
				$this->{$field_name} = $this->wakeUp($field_name, $value);
			}
		}
//		$this->afterObjectLoad();
		self::$object_cache[$class][$primary_value] = $this;
		self::$object_cache[$class][$primary_value]->afterObjectLoad();

		return $this;
	}


	//
	// Setter & Getter
	//
	/**
	 * @param int $id
	 */
	//	public function setId($id) {
	//		$this->id = $id;
	//	}
	/**
	 * @return int
	 */
	//	public function getId() {
	//		return $this->id;
	//	}
	/**
	 * @return mixed
	 */
	final public function getPrimaryFieldValue() {
		$primary_fieldname = self::returnPrimaryFieldName();

		return $this->{$primary_fieldname};
	}


	//
	// Reflection
	//
	/**
	 * @param ActiveRecord $obj
	 *
	 * @param null         $class
	 *
	 * @throws Exception
	 * @internal param null $foreign_object
	 *
	 * @return bool
	 */
	protected static function setDBFields($obj, $class = NULL) {
		$class = $class ? $class : get_class($obj);
		if (! self::$db_fields[$class]) {
			$fields = array();
			$primary = 0;
			foreach (self::getAttributesByFilter($obj, 'db') as $fieldname => $rf) {
				foreach ($rf as $k => $v) {
					self::checkAttribute($fieldname, 'db', $k);
				}
				if ($rf->db_has_field == 'true') {
					if ($rf->db_is_primary) {
						$primary ++;
						if ($primary > 1) {
							throw new Exception('Your Class \'' . __CLASS__ . '\' has two or more primary fields.');
						}
						if ($rf->db_length >= 1000) {
							throw new Exception('Your PrimaryKey \'' . $fieldname . '\' in Class \'' . __CLASS__
								. '\' is too long (max key length is 1000 bytes)');
						}
						self::$primary_fields[$class]['fieldname'] = $fieldname;
						self::$primary_fields[$class]['fieldtype'] = $rf->db_fieldtype;
					}
					$field_info = new stdClass();
					if (! in_array($rf->db_fieldtype, array(
						'text',
						'integer',
						'float',
						'date',
						'time',
						'timestamp',
						'clob'
					))
					) {
						throw new Exception('Your field \'' . $fieldname . '\' in Class \'' . __CLASS__ . '\' has wrong db_type: '
							. $rf->db_fieldtype);

						return;
					}
					switch ($rf->db_fieldtype) {
						case 'integer':
							$field_info->notnull = $rf->db_is_notnull == 'true' ? true : false;
							$field_info->length = in_array($rf->db_length, array( 1, 2, 3, 4, 8 )) ? $rf->db_length : 2;
							break;
						case 'text':
							$field_info->notnull = $rf->db_is_notnull == 'true' ? true : false;
							$field_info->length = ($rf->db_length > 0 AND $rf->db_length <= 4000) ? $rf->db_length : 1024;
							break;
						//	case 'date':
						//	case 'float':
						//	case 'time':
						//	case 'timestamp':
						//	case 'clob':
					}
					$field_info->db_type = $rf->db_fieldtype;
					$fields[$fieldname] = $field_info;
				}
			}
			self::$db_fields[$class] = $fields;
		}

		return true;
	}


	/**
	 * @param ActiveRecord $obj
	 * @param              $filter
	 *
	 * @return array
	 */
	protected static function getAttributesByFilter($obj, $filter) {
		$reflectionClass = new ReflectionClass($obj);
		$raw_fields = array();
		foreach ($reflectionClass->getProperties() as $property) {
			if ($property->getName() == 'fields') {
				continue;
			}
			$properties = new stdClass();
			$has_property = false;
			foreach (explode("\n", $property->getDocComment()) as $line) {
				if (preg_match("/[ ]*\\* @(" . $filter . "_[a-zA-Z0-9_]*)[ ]*([a-zA-Z0-9_]*)/u", $line, $matches)) {
					$has_property = true;
					$properties->{(string)$matches[1]} = $matches[2];
				}
			}
			if ($has_property) {
				$raw_fields[$property->getName()] = $properties;
			}
		}

		return $raw_fields;
	}


	/**
	 * @param ActiveRecord $obj
	 *
	 * @return bool
	 * @throws Exception
	 */
	private static function setFormFields(ActiveRecord $obj) {
		$class = get_class($obj);
		if (! self::$form_fields[$class]) {
			$fields = array();
			foreach (self::getAttributesByFilter($obj, 'form') as $fieldname => $rf) {
				foreach ($rf as $k => $v) {
					self::checkAttribute($fieldname, 'form', $k);
					if ($rf->form_has_field == 'true') {
						$field_info['type'] = $rf->form_type;
						$fields[$fieldname] = $field_info;
					}
				}
			}
			self::$form_fields[$class] = $fields;
		}

		return true;
	}


	/**
	 * @return array
	 */
	protected static function getPossibleFormAttributeNames() {
		return self::$possible_fields['form'];
	}


	/**
	 * @return array
	 */
	protected static function getPossibleDbAttributeNames() {
		return self::$possible_fields['db'];
	}


	/**
	 * @param $attribute_name
	 *
	 * @return bool
	 */
	protected static function isDbAttribute($attribute_name) {
		return in_array($attribute_name, self::getPossibleDbAttributeNames());
	}


	/**
	 * @param $attribute_name
	 *
	 * @return bool
	 */
	protected static function isFormAttribute($attribute_name) {
		return in_array($attribute_name, self::getPossibleFormAttributeNames());
	}


	/**
	 * @param $fieldname
	 * @param $type
	 * @param $attribute
	 *
	 * @throws Exception
	 */
	protected static function checkAttribute($fieldname, $type, $attribute) {
		$is_attribute = true;
		switch ($type) {
			case 'db':
				$is_attribute = self::isDbAttribute($attribute);
				break;
			case 'form':
				$is_attribute = self::isFormAttribute($attribute);
				break;
		}
		if (! $is_attribute) {
			throw new arException('Your field \'' . $fieldname . '\' in Class \'' . __CLASS__ . '\' has wrong attribute: ' . $attribute);
		}
	}


	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected static function fromCamelCase($str) {
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');

		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
}

?>
