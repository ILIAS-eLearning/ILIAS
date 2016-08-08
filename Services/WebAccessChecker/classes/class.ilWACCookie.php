<?php
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCookieInterface.php');

/**
 * Class ilWACCookie
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACCookie implements ilWACCookieInterface {

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
		return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}


	/**
	 * @param $name
	 * @return mixed
	 */
	public function get($name) {
		return $_COOKIE[$name];
	}


	/**
	 * @param $name
	 * @return bool
	 */
	public function exists($name) {
		return isset($_COOKIE[$name]);
	}
}
