<?php
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCookieInterface.php');

/**
 * Class ilWACDummyCookie
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACDummyCookie implements ilWACCookieInterface {

	/**
	 * @var array
	 */
	protected static $expires = array();
	/**
	 * @var array
	 */
	protected static $values = array();


	/**
	 * @param $name
	 * @param string $value
	 * @param int $expire
	 * @param string $path
	 * @param string $domain
	 * @param bool $secure
	 * @param bool $httponly
	 * @return bool
	 */
	public function set($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false) {
		static::$expires[$name] = $expire;
		static::$values[$name] = $value;
		$_COOKIE[$name] = $value;
	}


	/**
	 * @param $name
	 * @return mixed
	 */
	public function get($name) {
		if (!$this->exists($name)) {
			return false;
		}

		return static::$values[$name];
	}


	/**
	 * @param $name
	 * @return bool
	 */
	public function exists($name) {
		if (!isset(static::$expires[$name]) || (static::$expires[$name] !== 0 && static::$expires[$name] <= time())) {
			return false;
		}

		return isset(static::$values[$name]);
	}


	/**
	 * @return array
	 */
	public function getAll() {
		$return = array();
		foreach (self::$values as $key => $value) {
			if ($this->exists($key)) {
				$return[$key] = $value;
			}
		}

		return $return;
	}


	public static function clear() {
		self::$expires = array();
		self::$values = array();
	}
}
