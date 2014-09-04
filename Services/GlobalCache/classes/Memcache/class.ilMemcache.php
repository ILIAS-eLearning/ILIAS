<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');

/**
 * Class ilMemcache
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilMemcache extends ilGlobalCacheService {

	const STD_SERVER = '127.0.0.1';
	const STD_PORT = 11211;
	/**
	 * @var array
	 */
	protected static $servers = array(
		self::STD_SERVER => self::STD_PORT,
	);
	/**
	 * @var Memcached
	 */
	protected static $memcache_object;


	/**
	 * @param $service_id
	 * @param $component
	 */
	public function __construct($service_id, $component) {
		if (! (self::$memcache_object instanceof Memcached) AND $this->getInstallable()) {
			$memcached = new Memcached();
			foreach (self::$servers as $host => $port) {
				$memcached->addServer($host, $port);
			}
			self::$memcache_object = $memcached;
		}
		parent::__construct($service_id, $component);
	}


	/**
	 * @return Memcached
	 */
	protected function getMemcacheObject() {
		return self::$memcache_object;
	}


	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function exists($key) {
		return $this->getMemcacheObject()->get($this->returnKey($key)) != NULL;
	}


	/**
	 * @param      $key
	 * @param      $serialized_value
	 * @param null $ttl
	 *
	 * @return bool
	 */
	public function set($key, $serialized_value, $ttl = NULL) {
		return $this->getMemcacheObject()->set($this->returnKey($key), $serialized_value, $ttl);
	}


	/**
	 * @param      $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return $this->getMemcacheObject()->get($this->returnKey($key));
	}


	/**
	 * @param      $key
	 *
	 * @return bool
	 */
	public function delete($key) {
		return $this->getMemcacheObject()->delete($this->returnKey($key));
	}


	/**
	 * @return bool
	 */
	public function flush() {
		return $this->getMemcacheObject()->flush();
	}


	/**
	 * @return bool
	 */
	protected function getActive() {
		if ($this->getInstallable()) {
			$stats = $this->getMemcacheObject()->getStats();

			return $stats[self::STD_SERVER . ':' . self::STD_PORT]['pid'] > 0;
		}
	}


	/**
	 * @return bool
	 */
	protected function getInstallable() {
		return class_exists('Memcached');
	}


	/**
	 * @return string
	 */
	public function getInstallationFailureReason() {
		if ($this->getMemcacheObject() instanceof Memcached) {
			$stats = $this->getMemcacheObject()->getStats();

			if (! $stats[self::STD_SERVER . ':' . self::STD_PORT]['pid'] > 0) {
				return 'No Memcached-Server available';
			}
		}

		return parent::getInstallationFailureReason();
	}


	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function serialize($value) {
		return serialize($value);
	}


	/**
	 * @param $serialized_value
	 *
	 * @return mixed
	 */
	public function unserialize($serialized_value) {
		return unserialize($serialized_value);
	}


	/**
	 * @return array
	 */
	public function getInfo() {
		if (self::isInstallable()) {
			$return = array();
			$return['__cache_info'] = $this->getMemcacheObject()->getStats();
			foreach ($this->getMemcacheObject()->getAllKeys() as $key) {
				$return[$key] = $this->getMemcacheObject()->get($key);
			}

			return $return;
		}
	}
}

?>
