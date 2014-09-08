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
	 * @var ilGlobalCache
	 */
	protected $global_cache;


	/**
	 * @param $component
	 * @param $table_name
	 */
	protected function __construct($component, $table_name) {
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
			$this->global_cache->set($this->getTableName() . '_raw_data', $this->getRawData());
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
	 *
	 * @param bool $case_sensitive
	 *
	 * @return array
	 */
	public function getWhere($field, $value, $case_sensitive = true) {
		if (is_array($value)) {
			$index = md5(serialize($value));
		} else {
			$index = $value;
		}
		if (isset($this->cached_results[$field][$index])) {
			return $this->cached_results[$field][$index];
		}
		$result = array();
		foreach ($this->getRawData() as $dat) {
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
		if (count($result) == 1) {
			$result = $result[0];
		}

		$this->cached_results[$field][$index] = $result;
		$this->updateCachedResults();

		return $result;
	}


	protected function updateCachedResults() {
		$this->global_cache->set($this->getTableName() . '_cached_results', $this->getCachedResults());
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
}

?>
