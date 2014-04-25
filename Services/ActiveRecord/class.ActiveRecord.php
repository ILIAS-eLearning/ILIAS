<?php
require_once('class.ActiveRecordList.php');
require_once('Connector/class.arConnector.php');
require_once('Connector/class.arConnectorDB.php');
require_once('Cache/class.arObjectCache.php');
require_once('Fields/class.arFieldList.php');
require_once('Cache/class.arFieldCache.php');
require_once('Storage/int.arStorageInterface.php');

/**
 * Class ActiveRecord
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @experimental
 * @description
 *
 * @version 2.0.1
 *
 */
abstract class ActiveRecord implements arStorageInterface {

	const ACTIVE_RECORD_VERSION = '2.0.1';
	/**
	 * @var arConnector
	 */
	protected $arConnector;
	/**
	 * @var arFieldList
	 */
	protected $arFieldList;


	/**
	 * @return \arConnector
	 */
	public function getArConnector() {
		return $this->arConnector;
	}


	/**
	 * @return \arFieldList
	 */
	public function getArFieldList() {
		return $this->arFieldList;
	}


	/**
	 * @return string
	 * @description Return the Name of your Database Table
	 */
	abstract static function returnDbTableName();


	/**
	 * @return array
	 * @deprecated
	 */
	public static function returnDbFields() {
		$ar = self::getCalledClass();

		return arFieldCache::get($ar)->getArrayForConnector();
	}


	/**
	 * @return string
	 * @deprecated
	 */
	public static function returnPrimaryFieldName() {
		$ar = self::getCalledClass();

		return arFieldCache::getPrimaryFieldName($ar);
	}


	/**
	 * @return mixed
	 * @deprecated
	 */
	public static function returnPrimaryFieldType() {
		$ar = self::getCalledClass();

		return arFieldCache::getPrimaryFieldType($ar);
	}


	/**
	 * @return mixed
	 */
	final public function getPrimaryFieldValue() {
		$primary_fieldname = arFieldCache::getPrimaryFieldName($this);

		return $this->{$primary_fieldname};
	}


	/**
	 * @param int         $primary_key
	 * @param arConnector $connector
	 */
	public function __construct($primary_key = 0, arConnector $connector = NULL) {
		if ($connector == NULL) {
			$this->arConnector = new arConnectorDB();
		} else {
			$this->arConnector = $connector;
		}
		$this->arFieldList = arFieldCache::get($this);
		$key = $this->arFieldList->getPrimaryFieldName();
		$this->{$key} = $primary_key;

		if ($primary_key !== 0 AND $primary_key !== NULL AND $primary_key !== false) {
			$this->read();
		}
	}


	/**
	 * @param $id
	 */
	public function loadObject($id) {
		$class = get_class($this);
		if (! arObjectCache::isCached($class, $id)) {
			arObjectCache::store(new $class($id));
		}
	}


	/**
	 * @return array
	 */
	public function __asArray() {
		$return = array();
		foreach ($this->arFieldList->getFields() as $field) {
			$fieldname = $field->getName();
			$return[$fieldname] = $this->{$fieldname};
		}

		return $return;
	}


	/**
	 * @param array $array
	 *
	 * @return $this
	 */
	public function buildFromArray(array $array) {
		$class = get_class($this);
		$primary = $this->arFieldList->getPrimaryFieldName();
		$primary_value = $array[$primary];

		if ($primary_value AND arObjectCache::isCached($class, $primary_value)) {
			return arObjectCache::get($class, $primary_value);
		}
		foreach ($array as $field_name => $value) {
			if ($this->wakeUp($field_name, $value) === NULL) {
				$this->{$field_name} = $value;
			} else {
				$this->{$field_name} = $this->wakeUp($field_name, $value);
			}
		}
		arObjectCache::store($this);
		$this->afterObjectLoad();

		return $this;
	}


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
	 * @deprecated
	 */
	final public function getArrayForDb() {
		return $this->getArrayForConnector();
	}


	/**
	 * @return array
	 */
	final public function getArrayForConnector() {
		$data = array();
		foreach ($this->arFieldList->getFields() as $field) {
			$field_name = $field->getName();
			if ($this->sleep($field_name) === NULL) {
				$data[$field_name] = array( $field->getFieldType(), $this->{$field_name} );
			} else {
				$data[$field_name] = array( $field->getFieldType(), $this->sleep($field_name) );
			}
		}

		return $data;
	}




	//
	// Collector Modifications
	//

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
		return self::getCalledClass()->arConnector->renameField(self::getCalledClass(), $old_name, $new_name);
	}


	/**
	 * @return bool
	 */
	final public static function tableExists() {
		return self::getCalledClass()->arConnector->checkTableExists(self::getCalledClass());
	}


	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	final public static function fieldExists($field_name) {
		return self::getCalledClass()->arConnector->checkFieldExists(self::getCalledClass(), $field_name);
	}


	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	final public static function removeDBField($field_name) {
		return self::getCalledClass()->arConnector->removeField(self::getCalledClass(), $field_name);
	}


	/**
	 * @return bool
	 */
	final protected function installDatabase() {
		if (! $this->tableExists()) {
			$fields = array();
			foreach ($this->arFieldList->getFields() as $field) {
				$fields[$field->getName()] = $field->getAttributesForConnector();
			}

			return $this->arConnector->installDatabase($this, $fields);
		} else {
			return $this->arConnector->updateDatabase($this);
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

		return self::getCalledClass()->arConnector->updateDatabase(self::getCalledClass());
	}


	/**
	 * @return bool
	 */
	final public static function resetDB() {
		return self::getCalledClass()->arConnector->resetDatabase(self::getCalledClass());
	}


	/**
	 * @return bool
	 */
	final public static function truncateDB() {
		return self::getCalledClass()->arConnector->truncateDatabase(self::getCalledClass());
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
	/**
	 * @deprecated
	 */
	public function saveDeprecated() {
		if ($this->getId() === 0) {
			$this->create();
		} else {
			$this->update();
		}
	}


	public function create() {
		if (arFieldCache::getPrimaryFieldName($this) === 'id') {
			$this->id = $this->arConnector->nextID($this);
		}
		$this->arConnector->create($this, $this->getArrayForConnector());
		arObjectCache::store($this);
	}


	public function afterObjectLoad() {
	}


	public function read() {
		foreach ($this->arConnector->read($this) as $rec) {
			foreach ($this->getArrayForConnector() as $k => $v) {
				if ($this->wakeUp($k, $rec->{$k}) === NULL) {
					$this->{$k} = $rec->{$k};
				} else {
					$this->{$k} = $this->wakeUp($k, $rec->{$k});
				}
			}
			arObjectCache::store($this);
			$this->afterObjectLoad();
		}
	}


	public function update() {
		$this->arConnector->update($this);
		arObjectCache::store($this);
	}


	public function delete() {
		$this->arConnector->delete($this);
		arObjectCache::purge($this);
	}



	//
	// Collection
	//

	/**
	 * @param $id
	 *
	 * @return ActiveRecord
	 */
	public static function find($id) {
		/**
		 * @var $obj ActiveRecord
		 */
		$class = get_called_class();
		if (! arObjectCache::isCached($class, $id)) {
			$obj = new $class($id);
			$obj->loadObject($id);
		}

		return arObjectCache::get($class, $id);
	}


	/**
	 * @param      $where
	 * @param null $operator
	 *
	 * @return ActiveRecordList
	 */
	public static function where($where, $operator = NULL) {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());
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
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->join($ar, $on);
	}


	/**
	 * @param        $orderBy
	 * @param string $orderDirection
	 *
	 * @return ActiveRecordList
	 */
	public static function orderBy($orderBy, $orderDirection = 'ASC') {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());
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
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());
		$srModelObjectList->limit($start, $end);

		return $srModelObjectList;
	}


	/**
	 * @return int
	 */
	public static function affectedRows() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->affectedRows();
	}


	/**
	 * @return int
	 */
	public static function count() {
		return self::affectedRows();
	}


	/**
	 * @return ActiveRecord[]
	 */
	public static function get() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->get();
	}


	/**
	 * @return ActiveRecordList
	 */
	public static function debug() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->debug();
	}


	/**
	 * @return ActiveRecord
	 */
	public static function first() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->first();
	}


	/**
	 * @return ActiveRecordList
	 */
	public static function getCollection() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList;
	}


	/**
	 * @return ActiveRecord
	 */
	public static function last() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->last();
	}


	/**
	 * @return ActiveRecordList
	 */
	public static function getFirstFromLastQuery() {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->getFirstFromLastQuery();
	}


	/**
	 * @param arConnector $connector
	 *
	 * @return ActiveRecordList
	 */
	public static function connector(arConnector $connector) {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->connector($connector);
	}


	/**
	 * @param null $key
	 * @param null $values
	 *
	 * @return array
	 */
	public static function getArray($key = NULL, $values = NULL) {
		$srModelObjectList = new ActiveRecordList(self::getCalledClass());

		return $srModelObjectList->getArray($key, $values);
	}

	//
	// Magic Methods & Helpers
	//

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
			return self::where(array( self::fromCamelCase($matches[1]) => $arguments[0] ))->getFirst();
		}
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