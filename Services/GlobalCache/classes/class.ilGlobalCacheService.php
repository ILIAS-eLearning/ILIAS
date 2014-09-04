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
	protected static $active = array();
	/**
	 * @var bool
	 */
	protected static $installable = array();
	/**
	 * @var string
	 */
	protected $service_id = '';
	/**
	 * @var string
	 */
	protected $component = '';
	/**
	 * @var int
	 */
	protected $service_type = ilGlobalCache::TYPE_STATIC;


	/**
	 * @param $service_id
	 * @param $component
	 */
	public function __construct($service_id, $component) {
		$this->setComponent($component);
		$this->setServiceId($service_id);
		self::$active[get_called_class()] = $this->getActive();
		self::$installable[get_called_class()] = ($this->getInstallable() AND $this->checkMemory());
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
		return self::$active[get_called_class()];
	}


	/**
	 * @return bool
	 */
	public function isInstallable() {
		return self::$installable[get_called_class()];
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
	 * @return string
	 */
	public function getInstallationFailureReason() {
		if (! $this->getInstallable()) {
			return 'Not installed';
		}
		if (! $this->checkMemory()) {
			return 'Not enough Cache-Memory, set to at least ' . $this->getMinMemory() . 'M';
		}

		return 'Unknown reason';
	}


	/**
	 * @return int
	 */
	protected function getMemoryLimit() {
		return 9999;
	}


	/**
	 * @return int
	 */
	protected function getMinMemory() {
		return 0;
	}


	/**
	 * @return bool
	 */
	protected function checkMemory() {
		$matches = array();
		$memory_limit = $this->getMemoryLimit();
		if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
			switch ($matches[2]) {
				case 'M':
					$memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
					break;
				case 'K':
					$memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
					break;
			}
		}

		return ($memory_limit >= $this->getMinMemory() * 1024 * 1024);
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


	/**
	 * @param int $service_type
	 */
	public function setServiceType($service_type) {
		$this->service_type = $service_type;
	}


	/**
	 * @return int
	 */
	public function getServiceType() {
		return $this->service_type;
	}
}

?>
