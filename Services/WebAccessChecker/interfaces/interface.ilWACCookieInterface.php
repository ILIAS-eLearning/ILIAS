<?php

/**
 * Class ilWACCookieInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilWACCookieInterface {

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
	public function set($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false);


	/**
	 * @param $name
	 * @return mixed
	 */
	public function get($name);


	/**
	 * @param $name
	 * @return bool
	 */
	public function exists($name);
}
