<?php

/**
 * Class ilGlobalCacheService
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
abstract class ilGlobalCacheService {

	/**
	 * @var array
	 */
	protected static $valid_keys = array();
	/**
	 * @var bool
	 */
	protected static $active = false;
	/**
	 * @var bool
	 */
	protected static $installable = false;
	/**
	 * @var string
	 */
	protected $service_id = '';
	/**
	 * @var string
	 */
	protected $component = '';


	/**
	 * @param $service_id
	 * @param $component
	 */
	public function __construct($service_id, $component) {
		$this->setComponent($component);
		$this->setServiceId($service_id);
		self::$active = $this->getActive();
		self::$installable = $this->getInstallable();
		$this->readValid();
	}


	/**
	 * @return bool
	 */
	abstract protected function getActive();


	/**
	 * @return bool
	 */
	abstract protected function getInstallable();


	/**
	 * @return bool
	 *
	 * @description set self::$valid_keys from GlobalCache
	 */
	protected function readValid() {
		if ($this->isActive()) {
			self::$valid_keys = $this->unserialize($this->get('valid_keys'));
		}
	}


	/**
	 * @param $serialized_value
	 *
	 * @return mixed
	 */
	abstract public function unserialize($serialized_value);


	/**
	 * @param      $key
	 *
	 * @return mixed
	 */
	abstract public function get($key);


	/**
	 * @return string
	 */
	public function getServiceId() {
		return $this->service_id;
	}


	/**
	 * @param string $service_id
	 */
	public function setServiceId($service_id) {
		$this->service_id = $service_id;
	}


	public function __destruct() {
		$this->saveValid();
	}


	/**
	 * @return bool
	 *
	 * @description save self::$valid_keys to GlobalCache
	 */
	protected function saveValid() {
		if ($this->isActive()) {
			$this->set('valid_keys', $this->serialize(self::$valid_keys));
		}
	}


	/**
	 * @param      $key
	 * @param      $serialized_value
	 * @param null $ttl
	 *
	 * @return bool
	 */
	abstract public function set($key, $serialized_value, $ttl = NULL);


	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	abstract public function serialize($value);


	/**
	 * @param $key
	 *
	 * @return bool|void
	 */
	public function setValid($key) {
		self::$valid_keys[$this->getComponent()][$key] = true;
	}


	/**
	 * @return string
	 */
	public function getComponent() {
		return $this->component;
	}


	/**
	 * @param string $component
	 */
	public function setComponent($component) {
		$this->component = $component;
	}


	/**
	 * @param null $key
	 */
	public function setInvalid($key = NULL) {
		if ($key) {
			unset(self::$valid_keys[$this->getComponent()][$key]);
		} else {
			unset(self::$valid_keys[$this->getComponent()]);
		}
	}


	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function isValid($key) {
		return isset(self::$valid_keys[$this->getComponent()][$key]);
	}


	/**
	 * @return bool
	 */
	public function isActive() {
		return self::$active;
	}


	/**
	 * @return bool
	 */
	public function isInstallable() {
		return self::$installable;
	}


	/**
	 * @param $key
	 *
	 * @return string
	 */
	public function returnKey($key) {
		return str_replace('/', '_', $this->getServiceId() . '_' . $this->getComponent() . '_' . $key);
	}


	/**
	 * @return array
	 */
	public function getInfo() {
		return array();
	}


	/**
	 * @param $key
	 *
	 * @return bool
	 */
	abstract public function exists($key);


	/**
	 * @param      $key
	 *
	 * @return bool
	 */
	abstract public function delete($key);


	/**
	 * @return mixed
	 */
	abstract public function flush();
}

?>
