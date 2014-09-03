<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');

/**
 * Class ilApc
 *
 * @beta
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilApc extends ilGlobalCacheService {

	const CACHE_ID = 'user';


	/**
	 * @param $key
	 *
	 * @return bool|string[]
	 */
	public function exists($key) {
		return apc_exists($this->returnKey($key));
	}


	/**
	 * @param      $key
	 * @param      $serialized_value
	 * @param null $ttl
	 *
	 * @return array|bool
	 */
	public function set($key, $serialized_value, $ttl = NULL) {
		if ($this->exists($key)) {
			return apc_store($this->returnKey($key), $serialized_value, $ttl);
		} else {
			return apc_add($this->returnKey($key), $serialized_value, $ttl);
		}
	}


	/**
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return (apc_fetch($this->returnKey($key)));
	}


	/**
	 * @param $key
	 *
	 * @return bool|string[]
	 */
	public function delete($key) {
		return apc_delete($this->returnKey($key));
	}


	/**
	 * @return bool
	 */
	public function flush() {
		return apc_clear_cache(self::CACHE_ID);
	}


	/**
	 * @param $value
	 *
	 * @return mixed|string
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
		$iter = new APCIterator(self::CACHE_ID);
		$return = array();
		$match = "/" . $this->getServiceId() . "_" . $this->getComponent() . "_([_.a-zA-Z0-9]*)/uism";

		foreach ($iter as $item) {
			$key = $item['key'];
			//						echo '<pre>' . print_r($key, 1) . '</pre>';
			if (preg_match($match, $key, $matches)) {
				//				echo '<pre>' . print_r($matches, 1) . '</pre>';
				if ($matches[1]) {
					if ($this->isValid($matches[1])) {
						$return[$matches[1]] = $this->unserialize($item['value']);
					}
				}
			}
		}

		return $return;
	}


	protected function getActive() {
		return function_exists('apc_store');
	}


	/**
	 * @description set self::$installable
	 */
	protected function getInstallable() {
		return function_exists('apc_store');
	}
}

?>
