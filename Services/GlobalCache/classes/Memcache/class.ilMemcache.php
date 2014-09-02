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
	 * @var Memcached
	 */
	protected static $memcache_object;


	/**
	 * @param $service_id
	 * @param $component
	 */
	public function __construct($service_id, $component) {
		if (! (self::$memcache_object instanceof Memcached)) {
			$memcached = new Memcached();
			// $memcached->setOption(Memcached::OPT_CONNECT_TIMEOUT, 10);
			$memcached->addServer(self::STD_SERVER, self::STD_PORT);
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
		$stats = $this->getMemcacheObject()->getStats();

		return $stats[self::STD_SERVER . ':' . self::STD_PORT]['pid'] > 0;
	}


	/**
	 * @return bool
	 */
	protected function getInstallable() {
		return class_exists('Memcached');
	}


	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function serialize($value) {
		return ($value);
	}


	/**
	 * @param $serialized_value
	 *
	 * @return mixed
	 */
	public function unserialize($serialized_value) {
		return ($serialized_value);
	}


	public function getInfo() {
		echo '<pre>' . print_r($this->getMemcacheObject()->getStats(), 1) . '</pre>';

		//return $this->getMemcacheObject()->getAllKeys();
	}
}

?>
