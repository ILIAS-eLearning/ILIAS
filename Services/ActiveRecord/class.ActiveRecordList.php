<?php
require_once(dirname(__FILE__) . '/Connector/Join/class.arJoinCollection.php');
require_once(dirname(__FILE__) . '/Connector/Where/class.arWhereCollection.php');
require_once(dirname(__FILE__) . '/Connector/Limit/class.arLimitCollection.php');
require_once(dirname(__FILE__) . '/Connector/Order/class.arOrderCollection.php');

/**
 * Class ActiveRecordList
 *
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @description
 *
 * @version 2.0.2
 */
class ActiveRecordList {

	/**
	 * @var arWhereCollection
	 */
	protected $arWhereCollection;
	/**
	 * @var arJoinCollection
	 */
	protected $arJoinCollection;
	/**
	 * @var arOrderCollection
	 */
	protected $arOrderCollection;
	/**
	 * @var arLimitCollection
	 */
	protected $arLimitCollection;
	/**
	 * @var bool
	 */
	protected $loaded = false;
	/**
	 * @var string
	 */
	protected $class = '';
	/**
	 * @var ActiveRecord[]
	 */
	protected $result = array();
	/**
	 * @var array
	 */
	protected $result_array = array();
	/**
	 * @var bool
	 */
	protected $debug = false;
	/**
	 * @var null
	 */
	protected $date_format = NULL;
	/**
	 * @var string
	 */
	protected static $last_query;
	/**
	 * @var arConnector
	 */
	protected $connector;
	/**
	 * @var ActiveRecord
	 */
	protected $ar;


	/**
	 * @param ActiveRecord $ar
	 */
	public function __construct(ActiveRecord $ar) {
		$this->class = get_class($ar);
		$this->setAR($ar);
		$this->arWhereCollection = arWhereCollection::getInstance($this->getAR());
		$this->arJoinCollection = arJoinCollection::getInstance($this->getAR());
		$this->arLimitCollection = arLimitCollection::getInstance($this->getAR());
		$this->arOrderCollection = arOrderCollection::getInstance($this->getAR());
		if ($ar->getArConnector() == NULL) {
			$this->connector = new arConnectorDB($this);
		} else {
			$this->connector = $ar->getArConnector();
		}
	}


	//
	// Statements
	//

	/**
	 * @param      $where
	 * @param null $operator
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function where($where, $operator = NULL) {
		$this->loaded = false;
		if (is_string($where)) {
			$arWhere = new arWhere();
			$arWhere->setType(arWhere::TYPE_STRING);
			$arWhere->setStatement($where);
			$this->getArWhereCollection()->add($arWhere);

			return $this;
		} elseif (is_array($where)) {
			foreach ($where as $field_name => $value) {
				$arWhere = new arWhere();
				$arWhere->setFieldname($field_name);
				$arWhere->setValue($value);
				if ($operator) {
					if (is_array($operator)) {
						$arWhere->setOperator($operator[$field_name]);
					} else {
						$arWhere->setOperator($operator);
					}
				}
				$this->getArWhereCollection()->add($arWhere);
			}

			return $this;
		} else {
			throw new Exception('Wrong where Statement, use strings or arrays');
		}
	}


	/**
	 * @param        $order_by
	 * @param string $order_direction
	 *
	 * @return $this
	 */
	public function orderBy($order_by, $order_direction = 'ASC') {
		$arOrder = new arOrder();
		$arOrder->setFieldname($order_by);
		$arOrder->setDirection($order_direction);
		$this->getArOrderCollection()->add($arOrder);

		return $this;
	}


	/**
	 * @param $start
	 * @param $end
	 *
	 * @return $this
	 */
	public function limit($start, $end) {
		$arLimit = new arLimit();
		$arLimit->setStart($start);
		$arLimit->setEnd($end);

		$this->getArLimitCollection()->add($arLimit);

		return $this;
	}


	/**
	 * @param ActiveRecord $ar
	 * @param              $on_this
	 * @param              $on_external
	 * @param array        $fields
	 * @param string       $operator
	 *
	 * @return $this
	 */
	public function joinAR(ActiveRecord $ar, $on_this, $on_external, $fields = array( '*' ), $operator = '=') {
		return $this->join($ar::returnDbTableName(), $on_this, $on_external, $fields, $operator);
	}


	/**
	 * @param        $tablename
	 * @param        $on_this
	 * @param        $on_external
	 * @param array  $fields
	 * @param string $operator
	 *
	 * @return $this
	 */
	public function join($tablename, $on_this, $on_external, $fields = array( '*' ), $operator = '=') {
		$arJoin = new arJoin();
		$arJoin->setTableName($tablename);
		$arJoin->setOnFirstField($on_this);
		$arJoin->setOnSecondField($on_external);
		$arJoin->setOperator($operator);
		$arJoin->setFields($fields);

		$this->getArJoinCollection()->add($arJoin);

		return $this;
	}

	//
	// Statement Collections
	//

	/**
	 * @return arWhereCollection
	 */
	public function getArWhereCollection() {
		return $this->arWhereCollection;
	}


	/**
	 * @return arJoinCollection
	 */
	public function getArJoinCollection() {
		return $this->arJoinCollection;
	}


	/**
	 * @return arOrderCollection
	 */
	public function getArOrderCollection() {
		return $this->arOrderCollection;
	}


	/**
	 * @return arLimitCollection
	 */
	public function getArLimitCollection() {
		return $this->arLimitCollection;
	}

	//
	// Collection Functions
	//

	/**
	 * @param string $date_format
	 *
	 * @return $this
	 */
	public function dateFormat($date_format = 'd.m.Y - H:i:s') {
		$this->loaded = false;
		$this->setDateFormat($date_format);

		return $this;
	}


	public function debug() {
		$this->loaded = false;
		$this->debug = true;

		return $this;
	}


	/**
	 * @param arConnector $connector
	 *
	 * @return $this
	 */
	public function connector(arConnector $connector) {
		$this->connector = $connector;

		return $this;
	}


	/**
	 * @return bool
	 */
	public function hasSets() {
		return ($this->affectedRows() > 0) ? true : false;
	}


	/**
	 * @return int
	 */
	public function affectedRows() {
		return $this->connector->affectedRows($this);
	}


	/**
	 * @return int
	 */
	public function count() {
		return $this->affectedRows();
	}


	/**
	 * @return $this
	 */
	public function getCollection() {
		return $this;
	}


	/**
	 * @param string $class
	 */
	public function setClass($class) {
		$this->class = $class;
	}


	/**
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}


	/**
	 * @return array
	 */
	public function get() {
		$this->load();

		return $this->result;
	}


	/**
	 * @return ActiveRecord
	 */
	public function getFirstFromLastQuery() {
		$this->loadLastQuery();

		return array_shift(array_values($this->result));
	}


	/**
	 * @return ActiveRecord
	 */
	public function first() {
		$this->load();

		return array_shift(array_values($this->result));
	}


	/**
	 * @return ActiveRecord
	 */
	public function last() {
		$this->load();

		return array_pop(array_values($this->result));
	}


	/**
	 * @param string       $key    shall a specific value be used as a key? if null then the 1. array key is just increasing from 0.
	 * @param string|array $values which values should be taken? if null all are given. If only a string is given then the result is an 1D array!
	 *
	 * @return array
	 */
	public function getArray($key = NULL, $values = NULL) {
		$this->load();

		return $this->buildArray($key, $values);
	}


	/**
	 * @param $key
	 * @param $values
	 *
	 * @return array
	 * @throws Exception
	 */
	private function buildArray($key, $values) {
		if ($key === NULL AND $values === NULL) {
			return $this->result_array;
		}
		$array = array();
		foreach ($this->result_array as $row) {
			if ($key) {
				if (! array_key_exists($key, $row)) {
					throw new Exception("The attribute $key does not exist on this model.");
				}
				$array[$row[$key]] = $this->buildRow($row, $values);
			} else {
				$array[] = $this->buildRow($row, $values);
			}
		}

		return $array;
	}


	/**
	 * @param $row
	 * @param $values
	 *
	 * @return array
	 */
	private function buildRow($row, $values) {
		if ($values === NULL) {
			return $row;
		} else {
			$array = array();
			if (! is_array($values)) {
				return $row[$values];
			}
			foreach ($row as $key => $value) {
				if (in_array($key, $values)) {
					$array[$key] = $value;
				}
			}

			return $array;
		}
	}


	private function load() {
		if ($this->loaded) {
			return;
		} else {
			$records = $this->connector->readSet($this);
			foreach ($records as $res) {
				/**
				 * @var $obj ActiveRecord
				 */
				$class = get_class($this->getAR());
				$obj = new $class();
				$primaryFieldName = $obj->getArFieldList()->getPrimaryFieldName();
				$primary_field_value = $res[$primaryFieldName];
				$this->result[$primary_field_value] = $obj->buildFromArray($res);
				$res_awake = array();
				foreach ($res as $key => $value) {
					$arField = $obj->getArFieldList()->getFieldByName($key);
					if ($arField !== NULL) {
						if ($arField->isDateField() AND $this->getDateFormat()) {
							$res_awake[$key . '_unformatted'] = $value;
							$res_awake[$key . '_unix'] = strtotime($value);
							$value = date($this->getDateFormat(), strtotime($value));
						}
					}
					if ($this->getAR()->wakeUp($key, $value)) {
						$res_awake[$key] = $this->getAR()->wakeUp($key, $value);
					} else {
						$res_awake[$key] = $value;
					}
				}
				$this->result_array[$res_awake[$primaryFieldName]] = $res_awake;
			}
			$this->loaded = true;
		}
	}


	private function loadLastQuery() {
		$this->readFromDb(self::$last_query);
	}

	//
	// Setters & Getters
	//

	/**
	 * @param ActiveRecord $ar
	 */
	public function setAR($ar) {
		$this->ar = $ar;
	}


	/**
	 * @return ActiveRecord
	 */
	public function getAR() {
		return $this->ar;
	}


	/**
	 * @return boolean
	 */
	public function getDebug() {
		return $this->debug;
	}


	/**
	 * @param null $date_format
	 */
	public function setDateFormat($date_format) {
		$this->date_format = $date_format;
	}


	/**
	 * @return null
	 */
	public function getDateFormat() {
		return $this->date_format;
	}


	/**
	 * @param string $last_query
	 */
	public static function setLastQuery($last_query) {
		self::$last_query = $last_query;
	}


	/**
	 * @return string
	 */
	public static function getLastQuery() {
		return self::$last_query;
	}
}

?>