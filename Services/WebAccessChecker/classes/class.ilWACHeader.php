<?php
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACHeaderInterface.php');
require_once('./Services/WebAccessChecker/classes/class.ilHTTP.php');

/**
 * Class ilWACHeader
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACHeader implements ilWACHeaderInterface {

	/**
	 * @param $string
	 */
	public function sendHeader($string, $replace = true, $http_response_code = null) {
		header($string, $replace, $http_response_code);
	}


	/**
	 * @param $name
	 */
	public function sendStatusCode($name) {
		ilHTTP::status($name);
	}


	/**
	 * @param $name
	 * @return bool
	 */
	public function headerExists($name) {
		return in_array($name, headers_list());
	}


	/**
	 * @return array
	 */
	public function getSentHeaders() {
		return headers_list();
	}
}
