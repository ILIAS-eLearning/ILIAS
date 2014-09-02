<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');

/**
 * Class ilShm
 *
 * @beta
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilShm extends ilGlobalCacheService {

	/**
	 * @description set self::$active
	 */
	protected function getActive() {
		self::$active = function_exists('shm_put_var');
	}


	/**
	 * @description set self::$installable
	 */
	protected function getInstallable() {
		self::$active = function_exists('shm_put_var');
	}


	/**
	 * @var int
	 */
	protected static $id = 0;
	/**
	 * @var ressource
	 */
	protected static $ressource = NULL;


	/**
	 * @param $service_id
	 * @param $component
	 */
	public function __construct($service_id, $component) {
		parent::__construct($service_id, $component);
		$tmp = tempnam('/tmp', 'PHP');
		$key = ftok($tmp, 'a');
		self::$ressource = shm_attach($key);;
		self::$id = (int)self::$ressource;
	}


	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function exists($key) {
		return shm_has_var(self::$ressource, $key);
	}


	/**
	 * @param      $key
	 * @param      $serialized_value
	 * @param null $ttl
	 *
	 * @return bool
	 */
	public function set($key, $serialized_value, $ttl = NULL) {
		return shm_put_var(self::$ressource, $key, $serialized_value);
	}


	/**
	 * @param      $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return shm_get_var(self::$ressource, $key);
	}


	/**
	 * @param      $key
	 *
	 * @return bool
	 */
	public function delete($key) {
		return shm_remove_var(self::$ressource, $key);
	}


	/**
	 * @return bool
	 */
	public function flush() {
		shmop_delete(self::$id);

		return true;
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
}

?>
