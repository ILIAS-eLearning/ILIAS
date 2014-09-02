<?php
require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcache.php');
require_once('./Services/GlobalCache/classes/Xcache/class.ilXcache.php');
require_once('./Services/GlobalCache/classes/Shm/class.ilShm.php');
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
	const TYPE_STATIC = - 1;
	const TYPE_XCACHE = 1;
	const TYPE_MEMCACHED = 2;
	const TYPE_SHM = 3;
	const TYPE_APC = 4;
	const TYPE_FALLBACK = self::TYPE_STATIC;
	const COMP_LNG = 'lng';
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
	protected static $types = array( self::TYPE_MEMCACHED, self::TYPE_XCACHE, self::TYPE_SHM, self::TYPE_APC, self::TYPE_STATIC );
	/**
	 * @var array
	 */
	protected static $registred_components = array(
		self::COMP_LNG,
		self::COMP_OBJ_DEF,
		self::COMP_SETTINGS,
		self::COMP_TEMPLATE,
		self::COMP_ILCTRL,
		self::COMP_PLUGINS,
		self::COMP_PLUGINSLOTS,
		self::COMP_COMPONENT,
		self::COMP_RBAC_UA,
//		'ctrl_mm',
	);
	/**
	 * @var array
	 */
	protected static $registred_types = array(
		self::COMP_LNG => self::TYPE_APC,
		self::COMP_OBJ_DEF => self::TYPE_APC,
		self::COMP_SETTINGS => self::TYPE_APC,
		self::COMP_TEMPLATE => self::TYPE_APC,
		self::COMP_ILCTRL => self::TYPE_APC,
		self::COMP_PLUGINS => self::TYPE_APC,
		self::COMP_PLUGINSLOTS => self::TYPE_APC,
		self::COMP_COMPONENT => self::TYPE_APC,
		self::COMP_RBAC_UA => self::TYPE_APC,
//		'ctrl_mm' => self::TYPE_APC,
	);
	/**
	 * @var array
	 */
	protected static $active_types = array(
		self::COMP_LNG,
		self::COMP_OBJ_DEF,
		self::COMP_SETTINGS,
		self::COMP_TEMPLATE,
		self::COMP_ILCTRL,
		self::COMP_PLUGINS,
		self::COMP_COMPONENT,
		self::COMP_RBAC_UA,
		self::COMP_PLUGINSLOTS,
		//		'ctrl_mm',

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
	protected $active = false;


	/**
	 * @param $component
	 *
	 * @return int
	 */
	protected static function getComponentType($component) {
		$comp_setting = self::$registred_types[$component];

		return $comp_setting ? $comp_setting : self::TYPE_FALLBACK;
	}


	/**
	 * @param null $component
	 *
	 * @return ilGlobalCache
	 */
	public static function getInstance($component = NULL) {
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
		foreach (self::$types as $type) {
			$obj = new self($type);
			if ($obj->isInstallable()) {
				$types[] = $obj;
			}
		}

		return $types;
	}


	/**
	 * @param $type
	 * @param $component
	 */
	protected function __construct($type, $component = NULL) {
		$this->setComponent($component);
		$service_id = substr($shm_key = ftok(__FILE__, 't'), 0, 6);
		$this->setServiceid($service_id);
		$this->setActive(in_array($component, self::$active_types));
		switch ($type) {
			case self::TYPE_APC:
				$this->global_cache = new ilApc($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_MEMCACHED:
				$this->global_cache = new ilMemcache($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_XCACHE:
				$this->global_cache = new ilXcache($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_SHM:
				$this->global_cache = new ilShm($this->getServiceid(), $this->getComponent());
				break;
			case self::TYPE_STATIC:
				$this->global_cache = new ilStaticCache($this->getServiceid(), $this->getComponent());
				break;
		}
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		if (! self::ACTIVE) {
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
			if ($this->global_cache->isValid($key)) {
				return $unserialized_return;
			}
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
}

?>
