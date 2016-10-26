<?php
require_once('./Services/WebAccessChecker/interfaces/interface.ilWACHeaderInterface.php');

/**
 * Class ilWACDummyHeader
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilWACDummyHeader implements ilWACHeaderInterface {

	/**
	 * @var array
	 */
	protected static $sent_headers = array();
	/**
	 * @var int
	 */
	protected static $status_code = 0;


	/**
	 * @param $string
	 * @param bool $replace
	 * @param null $http_response_code
	 */
	public function sendHeader($string, $replace = true, $http_response_code = null) {
		preg_match("/(?P<key>.*):(?P<value>.*)/um", $string, $matches);

		$replaced = false;
		if ($replace) {
			foreach (self::$sent_headers as $i => $sent_header) {
				preg_match("/(?P<key>.*):(?P<value>.*)/um", $sent_header, $m);
				if ($m['key'] == $matches['key']) {
					$replaced = true;
					self::$sent_headers[$i] = $string;
					break;
				}
			}
		}
		if (!$replaced || $replace) {
			self::$sent_headers[] = $string;
		}

		if ($http_response_code) {
			$this->sendStatusCode($http_response_code);
		}
	}


	/**
	 * @param $name
	 */
	public function sendStatusCode($name) {
		self::$status_code = $name;
	}


	/**
	 * @param $name
	 * @return bool
	 */
	public function headerExists($name) {
		return in_array($name, self::$sent_headers);
	}


	/**
	 * @return array
	 */
	public function getSentHeaders() {
		return self::$sent_headers;
	}


	public static function clear() {
		self::$sent_headers = array();
		self::$status_code = 0;
	}
}
