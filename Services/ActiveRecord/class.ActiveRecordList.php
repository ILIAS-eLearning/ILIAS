<?php

/**
 * Class ActiveRecordList
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 *
 * @description
 *
 * @version 1.0.14
 */
class ActiveRecordList {

	/**
	 * @var array
	 */
	protected $where = array();
	/**
	 * @var array
	 */
	protected $joins = array();
	/**
	 * @var bool
	 */
	protected $loaded = false;
	/**
	 * @var string
	 */
	protected $order_by = '';
	/**
	 * @var string
	 */
	protected $order_direction = 'ASC';
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
	 * @var array
	 */
	protected $string_wheres = array();
	/**
	 * @var
	 */
	protected $start;
	/**
	 * @var
	 */
	protected $end;
	/**
	 * @var bool
	 */
	protected $debug = false;
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
		$this->ar = $ar;
		if ($ar->getArConnector() == NULL) {
			$this->connector = new arConnectorDB($this);
		} else {
			$this->connector = $ar->getArConnector();
		}
	}


	/**
	 * @param \ActiveRecord $ar
	 */
	public function setAR($ar) {
		$this->ar = $ar;
	}


	/**
	 * @return \ActiveRecord
	 */
	public function getAR() {
		return $this->ar;
	}


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
			$this->string_wheres[] = $where; // FSX SQL-Injection abfangen
			return $this;
		} elseif (is_array($where)) {
			foreach ($where as $field_name => $value) {
				$op = '=';
				if ($operator !== NULL) {
					if (is_array($operator)) {
						$op = $operator[$field_name];
					} else {
						$op = $operator;
					}
				}
				$this->where[] = array( 'fieldname' => $field_name, 'value' => $value, 'operator' => $op );
			}

			return $this;
		} else {
			throw new Exception('Wrong where Statement, use strings or arrays');
		}
	}


	/**
	 * @param        $orderBy
	 * @param string $orderDirection
	 *
	 * @return $this
	 */
	public function orderBy($orderBy, $orderDirection = 'ASC') {
		$this->loaded = false;
		$this->order_by = $orderBy;
		$this->order_direction = $orderDirection;

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
	 * @param $start
	 * @param $end
	 *
	 * @return $this
	 */
	public function limit($start, $end) {
		$this->loaded = false;
		$this->start = $start;
		$this->end = $end;

		return $this;
	}


	/**
	 * @return array
	 */
	public function getWhere() {
		return $this->where;
	}


	/**
	 * @return array
	 */
	public function getJoins() {
		return $this->joins;
	}


	/**
	 * @return array
	 */
	public function getStringWheres() {
		return $this->string_wheres;
	}


	/**
	 * @return boolean
	 */
	public function getDebug() {
		return $this->debug;
	}


	/**
	 * @return string
	 */
	public function getOrderBy() {
		return $this->order_by;
	}


	/**
	 * @return string
	 */
	public function getOrderDirection() {
		return $this->order_direction;
	}


	/**
	 * @return mixed
	 */
	public function getStart() {
		return $this->start;
	}


	/**
	 * @return mixed
	 */
	public function getEnd() {
		return $this->end;
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
	 * @return bool
	 */
	public function hasSets() {
		return ($this->affectedRows() > 0) ? true : false;
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
	 * @param ActiveRecord $ar
	 * @param array        $on
	 *
	 * @return $this
	 */
	public function join(ActiveRecord $ar, $on = array()) {
		$this->joins[$ar::returnDbTableName()] = $on;

		return $this;
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
				$obj = new $this->class();

				$primaryFieldName = $obj->getArFieldList()->getPrimaryFieldName();
				$primary_field_value = $res[$primaryFieldName];
				$this->result[$primary_field_value] = $obj->buildFromArray($res);
				$res_awake = array();
				foreach ($res as $key => $value) {
					if ($this->getAR()->wakeUp($key, $value)) {
						$res_awake[$key] = $this->getAR()->wakeUp($key, $value);
					} else {
						$res_awake[$key] = $value;
					}
				}
				$this->result_array[$res_awake[$primaryFieldName]] = $res_awake;
				// $this->result_array[$res[$primaryFieldName]] = $res;
			}
			$this->loaded = true;
		}
	}


	private function loadLastQuery() {
		$this->readFromDb(self::$last_query);
	}
}

?>