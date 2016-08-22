<?php

/**
 * Class ilWACHeaderInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilWACHeaderInterface {

	/**
	 * @param $string
	 * @param bool $replace
	 * @param null $http_response_code
	 */
	public function sendHeader($string, $replace = true, $http_response_code = null);


	/**
	 * @param $name
	 */
	public function sendStatusCode($name);


	/**
	 * @param $name
	 * @return bool
	 */
	public function headerExists($name);


	/**
	 * @return array
	 */
	public function getSentHeaders();
}
