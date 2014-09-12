<?php
require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');

/**
 * Class ilGlobalCacheDBLayer
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheDBLayer {

	/**
	 * @var string
	 */
	protected $component = '';
	/**
	 * @var bool
	 */
	protected $loaded = false;
	/**
	 * @var string
	 */
	protected $table_name = '';
	/**
	 * @var ilGlobalCacheDBLayer[]
	 */
	protected static $instances = array();
	/**
	 * @var array
	 */
	protected $raw_data = array();
	/**
	 * @var array
	 */
	protected $cached_results = array();
	/**
	 * @var int
	 */
	protected $ttl = NULL;
	/**
	 * @var ilGlobalCache
	 */
	protected $global_cache;
	/**
	 * @var ilGcDbWhere[]
	 */
	protected $wheres = array();


	/**
	 * @param     $component
	 * @param     $table_name
	 * @param int $ttl
	 */
	protected function __construct($component, $table_name, $ttl = NULL) {
		$this->setTtl($ttl);
		$this->setComponent($component);
		$this->setTableName($table_name);
		$this->global_cache = ilGlobalCache::getInstance($component);
		$this->readFromCache();
		if (! $this->getLoaded()) {
			$this->readFromDB();
			$this->writeToCache();
			$this->setLoaded(true);
		}
	}


	protected function readFromCache() {
		if ($this->global_cache->isActive()) {
			$data = $this->global_cache->get($this->getTableName() . '_raw_data');
			$cached_results = $this->global_cache->get($this->getTableName() . '_cached_results');
			if (is_array($data)) {
				$this->setRawData($data);
				$this->setCachedResults($cached_results);
				$this->setLoaded(true);
			}
		}
	}


	protected function writeToCache() {
		if ($this->global_cache->isActive()) {
			$this->global_cache->set($this->getTableName() . '_raw_data', $this->getRawData(), $this->getTtl());
			$this->updateCachedResults();
		}
	}


	protected function readFromDB() {
		global $ilDB;
		/**
		 * @var $ilDB ilDB
		 */
		$q = 'SELECT * FROM ' . $this->getTableName();
		$res = $ilDB->query($q);
		$raw_data = array();
		while ($set = $ilDB->fetchObject($res)) {
			$raw_data[] = $set;
		}
		$this->setRawData($raw_data);
	}


	/**
	 * @param      $field
	 * @param      $value
	 * @param bool $case_sensitive
	 *
	 * @return array
	 */
	public function getWhere($field, $value, $case_sensitive = true) {
		return $this->filter($this->getRawData(), $field, $value, $case_sensitive);
	}


	/**
	 * @param array $data
	 * @param       $field
	 * @param       $value
	 *
	 * @param bool  $case_sensitive
	 *
	 * @param bool  $strip
	 *
	 * @return array
	 */
	public function filter(array $data, $field, $value, $case_sensitive = true, $strip = true) {
		if (is_array($value)) {
			$index = md5(serialize($value)) . $case_sensitive;
		} else {
			$index = $value . $case_sensitive;
		}
		if (isset($this->cached_results[$this->getTableName()][$field][$index])) {
			return $this->cached_results[$this->getTableName()][$field][$index];
		}
		$result = array();
		foreach ($data as $dat) {
			if ($case_sensitive) {
				if (is_array($value)) {
					if (in_array($dat->{$field}, $value)) {
						$result[] = $dat;
					}
				} elseif ($dat->{$field} == $value) {

					$result[] = $dat;
				}
			} else {
				if (is_array($value)) {
					if (preg_grep("/" . $dat->{$field} . "/i", $value)) {
						$result[] = $dat;
					}
				} elseif (strcasecmp($dat->{$field}, $value) == 0) {
					$result[] = $dat;
				}
			}
		}
		if (count($result) == 1 AND $strip) {
			$result = $result[0];
		}

		$this->cached_results[$this->getTableName()][$field][$index] = $result;
		$this->updateCachedResults();

		return $result;
	}


	protected function updateCachedResults() {
		$this->global_cache->set($this->getTableName() . '_cached_results', $this->getCachedResults());
	}


	/**
	 * @param      $field
	 * @param      $value
	 * @param bool $case_sensitive
	 *
	 * @return $this
	 */
	public function where($field, $value, $case_sensitive = true) {
		$ilGcDbWhere = new ilGcDbWhere();
		$ilGcDbWhere->setField($field);
		$ilGcDbWhere->setValue($value);
		$ilGcDbWhere->setCaseSensitive($case_sensitive);
		$this->wheres[] = $ilGcDbWhere;

		return $this;
	}


	/**
	 * @param bool $strip
	 *
	 * @return array
	 */
	public function get($strip = true) {
		$result = $this->getRawData();
		foreach ($this->wheres as $ilGcDbWhere) {
			$result = $this->filter($result, $ilGcDbWhere->getField(), $ilGcDbWhere->getValue(), $ilGcDbWhere->getCaseSensitive(), false);
		}

		if (count($result) == 1 AND $strip) {
			//			echo '<pre>' . print_r($result, 1) . '</pre>';
			$result = $result[0];
		}

		return $result;
	}


	/**
	 * @param $component
	 * @param $table_name
	 *
	 * @return ilGlobalCacheDBLayer
	 */
	public static function getInstance($component, $table_name) {
		if (! isset(self::$instances[$component . $table_name])) {
			self::$instances[$component . $table_name] = new self($component, $table_name);
		}

		return self::$instances[$component . $table_name];
	}


	/**
	 * @param array $cached_results
	 */
	public function setCachedResults($cached_results) {
		$this->cached_results = $cached_results;
	}


	/**
	 * @return array
	 */
	public function getCachedResults() {
		return $this->cached_results;
	}


	/**
	 * @param array $raw_data
	 */
	public function setRawData($raw_data) {
		$this->raw_data = $raw_data;
	}


	/**
	 * @return array
	 */
	public function getRawData() {
		return $this->raw_data;
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
	 * @param boolean $loaded
	 */
	public function setLoaded($loaded) {
		$this->loaded = $loaded;
	}


	/**
	 * @return boolean
	 */
	public function getLoaded() {
		return $this->loaded;
	}


	/**
	 * @param string $component
	 */
	public function setComponent($component) {
		$this->component = $component;
	}


	/**
	 * @return string
	 */
	public function getComponent() {
		return $this->component;
	}


	/**
	 * @param int $ttl
	 */
	public function setTtl($ttl) {
		$this->ttl = $ttl;
	}


	/**
	 * @return int
	 */
	public function getTtl() {
		return $this->ttl;
	}
}

class ilGcDbWhere {

	/**
	 * @var string
	 */
	protected $field = '';
	/**
	 * @var string|array
	 */
	protected $value;
	/**
	 * @var bool
	 */
	protected $case_sensitive = true;


	/**
	 * @param boolean $case_sensitive
	 */
	public function setCaseSensitive($case_sensitive) {
		$this->case_sensitive = $case_sensitive;
	}


	/**
	 * @return boolean
	 */
	public function getCaseSensitive() {
		return $this->case_sensitive;
	}


	/**
	 * @param string $field
	 */
	public function setField($field) {
		$this->field = $field;
	}


	/**
	 * @return string
	 */
	public function getField() {
		return $this->field;
	}


	/**
	 * @param array|string $value
	 */
	public function setValue($value) {
		$this->value = $value;
	}


	/**
	 * @return array|string
	 */
	public function getValue() {
		return $this->value;
	}
}


?>
