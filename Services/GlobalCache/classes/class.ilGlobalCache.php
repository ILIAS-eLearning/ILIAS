<?php
require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcache.php');
require_once('./Services/GlobalCache/classes/Xcache/class.ilXcache.php');
require_once('./Services/GlobalCache/classes/Apc/class.ilApc.php');
require_once('./Services/GlobalCache/classes/Static/class.ilStaticCache.php');

/**
 * Class ilGlobalCache
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCache {

	const MSG = 'Global Cache not active, can not access cache';
	const ACTIVE = true;
	const TYPE_STATIC = 0;
	const TYPE_XCACHE = 1;
	const TYPE_MEMCACHED = 2;
	const TYPE_APC = 3;
	const TYPE_FALLBACK = self::TYPE_STATIC;
	const COMP_LNG = 'lng';
	const COMP_CLNG = 'clng';
	const COMP_OBJ_DEF = 'obj_def';
	const COMP_SETTINGS = 'set';
	const COMP_TEMPLATE = 'tpl';
	const COMP_ILCTRL = 'ilctrl';
	const COMP_PLUGINS = 'plugins';
	const COMP_PLUGINSLOTS = 'pluginslots';
	const COMP_COMPONENT = 'comp';
	const COMP_RBAC_UA = 'rbac_ua';
	/**
	 * @var array
	 */
	protected static $types = array(
		self::TYPE_MEMCACHED,
		//		self::TYPE_XCACHE,
		self::TYPE_APC,
		self::TYPE_STATIC
	);
	/**
	 * @var array
	 */
	protected static $registred_components = array(
		self::COMP_LNG,
		self::COMP_CLNG,
		self::COMP_OBJ_DEF,
		self::COMP_SETTINGS,
		self::COMP_TEMPLATE,
		self::COMP_ILCTRL,
				self::COMP_COMPONENT,
		//		self::COMP_PLUGINS,
		//		self::COMP_PLUGINSLOTS,
//		self::COMP_RBAC_UA,
	);
	/**
	 * @var array
	 */
	protected static $active_types = array(
		self::COMP_LNG,
		self::COMP_CLNG,
		self::COMP_OBJ_DEF,
		self::COMP_ILCTRL,
		self::COMP_COMPONENT,
		self::COMP_TEMPLATE,
		self::COMP_SETTINGS,
		self::COMP_PLUGINS,
		self::COMP_PLUGINSLOTS,
//		self::COMP_RBAC_UA,
		'ctrl_mm'
	);
	/**
	 * @var ilGlobalCache
	 */
	protected static $instances;
	/**
	 * @var ilGlobalCacheService
	 */
	protected $global_cache;
	/**
	 * @var string
	 */
	protected $service_id = '';
	/**
	 * @var string
	 */
	protected $component;
	/**
	 * @var bool
	 */
	protected $active = true;
	/**
	 * @var int
	 */
	protected $service_type = ilGlobalCache::TYPE_STATIC;


	/**
	 * @param $component
	 *
	 * @return int
	 */
	protected static function getComponentType($component = NULL) {
		/**
		 * @var $ilClientIniFile ilIniFile
		 */
		global $ilClientIniFile;
		$service_type = $ilClientIniFile->readVariable('cache', 'global_cache_service_type');
		if ($service_type) {
			return $service_type;
		}

		return self::TYPE_FALLBACK;
	}


	/**
	 * @param null $component
	 *
	 * @return ilGlobalCache
	 */
	public static function getInstance($component) {
		if (! isset(self::$instances[$component])) {
			$type = self::getComponentType($component);
			$ilGlobalCache = new self($type, $component);

			self::$instances[$component] = $ilGlobalCache;
		}

		return self::$instances[$component];
	}


	/**
	 * @return ilGlobalCache[]
	 */
	public static function getAllInstallableTypes() {
		$types = array();
		foreach (self::getAllTypes() as $type) {
			if ($type->isCacheServiceInstallable()) {
				$types[] = $type;
			}
		}

		return $types;
	}


	/**
	 * @return ilGlobalCache[]
	 */
	public static function getAllTypes() {
		$types = array();
		foreach (self::$types as $type) {
			$obj = new self($type);
			$types[$type] = $obj;
		}

		return $types;
	}


	/**
	 * @param $service_type_id
	 * @param $component
	 */
	protected function __construct($service_type_id, $component = NULL) {
		$this->setComponent($component);
		/*
		if (function_exists('ftok')) {
			$service_id = substr($shm_key = ftok(__FILE__, 't'), 0, 6);
		} else {
			$service_id = ILIAS_CLIENT_ID;
		}
		*/
		$service_id = 'ilias';
		$this->setServiceid($service_id);
		$this->setActive(in_array($component, self::$active_types));
		switch ($service_type_id) {
			case self::TYPE_APC:
				$this->global_cache = new ilApc($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_MEMCACHED:
				$this->global_cache = new ilMemcache($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_XCACHE:
				$this->global_cache = new ilXcache($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_STATIC:
				$this->global_cache = new ilStaticCache($this->getServiceid(), $this->getComponent());
				break;
		}
		$this->global_cache->setServiceType($service_type_id);
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		if (! self::ACTIVE) {

			return false;
		}
		/**
		 * @var $ilClientIniFile ilIniFile
		 */
		global $ilClientIniFile;
		if ($ilClientIniFile->readVariable('cache', 'activate_global_cache') != '1') {
			return false;
		}
		if (! $this->getActive()) {
			return false;
		}

		return $this->global_cache->isActive();
	}


	/**
	 * @return bool
	 */
	public function isInstallable() {
		return count(self::getAllInstallableTypes()) > 0;
	}


	/**
	 * @return bool
	 */
	public function isCacheServiceInstallable() {
		return $this->global_cache->isInstallable();
	}


	/**
	 * @return string
	 */
	public function getInstallationFailureReason() {
		return $this->global_cache->getInstallationFailureReason();
	}


	/**
	 * @param $key
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	public function exists($key) {
		if (! $this->global_cache->isActive()) {
			throw new RuntimeException(self::MSG);
		}

		return $this->global_cache->exists($key);
	}


	/**
	 * @param      $key
	 * @param      $value
	 * @param null $ttl
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	public function set($key, $value, $ttl = NULL) {
		if (! $this->isActive()) {

			return false;
			//throw new RuntimeException(self::MSG . '. Key: ' . $key);
		}
		$this->global_cache->setValid($key);

		return $this->global_cache->set($key, $this->global_cache->serialize($value), $ttl);
	}


	/**
	 * @param $key
	 *
	 * @throws RuntimeException
	 * @return mixed
	 */
	public function get($key) {
		if (! $this->isActive()) {
			return false;

			throw new RuntimeException(self::MSG . '. get Key: ' . $key);
		}
		$unserialized_return = $this->global_cache->unserialize($this->global_cache->get($key));
		if ($unserialized_return) {
			//if (!$this->global_cache->isValid($key)) {
			return $unserialized_return;
			//}
		}

		return NULL;
	}


	/**
	 * @param $key
	 *
	 * @throws RuntimeException
	 * @return bool
	 */
	public function delete($key) {
		if (! $this->isActive()) {

			return false;
			throw new RuntimeException(self::MSG);
		}

		return $this->global_cache->delete($key);
	}


	/**
	 * @param bool $complete
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function flush($complete = false) {
		if ($this->global_cache->isActive()) {
			if ($complete) {
				return $this->global_cache->flush();
			} else {
				$this->global_cache->setInvalid();
			}
		}

		return false;
	}


	public function getInfo() {
		return $this->global_cache->getInfo();
	}


	/**
	 * @param string $service_id
	 */
	public function setServiceid($service_id) {
		$this->service_id = $service_id;
	}


	/**
	 * @return string
	 */
	public function getServiceid() {
		return $this->service_id;
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
	 * @param boolean $active
	 */
	public function setActive($active) {
		$this->active = $active;
	}


	/**
	 * @return boolean
	 */
	public function getActive() {
		return $this->active;
	}


	/**
	 * @param int $service_type
	 */
	public function setServiceType($service_type) {
		$this->global_cache->setServiceType($service_type);
	}


	/**
	 * @return int
	 */
	public function getServiceType() {
		return $this->global_cache->getServiceType();
	}
}

?>
