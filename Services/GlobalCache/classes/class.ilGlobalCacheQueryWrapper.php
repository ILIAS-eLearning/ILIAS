<?php
require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');

/**
 * Class ilGlobalCacheQueryWrapper
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheQueryWrapper {

	const FETCH_TYPE_ASSOC = 1;
	const FETCH_TYPE_OBJECT = 2;
	const MODE_SINGLE = 1;
	const MODE_MULTIPLE = 2;
	/**
	 * @var string
	 */
	protected $query = '';
	/**
	 * @var string
	 */
	protected $cache_key = '';
	/**
	 * @var int
	 */
	protected $fetch_type = self::FETCH_TYPE_OBJECT;
	/**
	 * @var int
	 */
	protected $mode = self::MODE_SINGLE;


	/**
	 * @param     $cache_key
	 * @param     $query
	 * @param int $fetch_type
	 * @param int $mode
	 */
	public function __construct($cache_key, $query, $fetch_type = self::FETCH_TYPE_OBJECT, $mode = self::MODE_SINGLE) {
		$this->cache_key = $cache_key;
		$this->fetch_type = $fetch_type;
		$this->mode = $mode;
		$this->query = $query;
	}


	/**
	 * @return array|mixed
	 */
	public function get() {
		$ilGlobalCache = ilGlobalCache::getInstance();
		if ($ilGlobalCache->isActive()) {
			$rec = $ilGlobalCache->get($this->cache_key);
			if (! $rec) {
				$rec = $this->getFromDb();
				$ilGlobalCache->set($this->cache_key, $rec, 600);
			}
		} else {
			$rec = $this->getFromDb();
		}

		return $rec;
	}


	/**
	 * @return array
	 */
	protected function getFromDb() {
		global $ilDB;
		/**
		 * @var ilDB
		 */
		$return = array();
		$res = $ilDB->query($this->query);
		switch ($this->getFetchType()) {
			case self::FETCH_TYPE_OBJECT:
				while ($rec = $ilDB->fetchObject($res)) {
					$return[] = $rec;
				}
				break;
			case self::FETCH_TYPE_ASSOC:
				while ($rec = $ilDB->fetchAssoc($res)) {
					$return[] = $rec;
				}
				break;
		}

		if ($this->getMode() == self::MODE_SINGLE) {
			return $return[0];
		} else {
			return $return;
		}
	}


	/**
	 * @param int $fetch_type
	 */
	public function setFetchType($fetch_type) {
		$this->fetch_type = $fetch_type;
	}


	/**
	 * @return int
	 */
	public function getFetchType() {
		return $this->fetch_type;
	}


	/**
	 * @param int $mode
	 */
	public function setMode($mode) {
		$this->mode = $mode;
	}


	/**
	 * @return int
	 */
	public function getMode() {
		return $this->mode;
	}
}

?>
