<?php

/**
 * srWebAccessChecker
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.4
 */
class srWebAccessChecker {

	public function __construct() {
	}


	public function checkAccess() {
	}


	/**
	 * @return string
	 */
	private static function getLinkRoot() {
		return ILIAS_HTTP_PATH . strstr(__FILE__, '/Customizing');
	}


	/**
	 * @param $obj_type
	 * @param $ref_id
	 * @param $sub_type
	 * @param $sub_id
	 * @param $version
	 *
	 * @return string
	 */
	public static function getLink($obj_type, $ref_id, $sub_type, $sub_id, $version = NULL) {
		$arr_append = array(
			'obj_type' => $obj_type,
			'ref_id' => $ref_id,
			'sub_type' => $sub_type,
			'sub_id' => $sub_id
		);
		$str_append = array();
		foreach ($arr_append as $k => $v) {
			$str_append[] = $k . '=' . $v;
		}

		return self::getLinkRoot() . '?' . implode('&', $str_append);
	}


	/**
	 * @param      $class
	 * @param      $id
	 * @param null $ref_id
	 *
	 * @return string
	 */
	public static function getLinkByClass($class, $id, $ref_id = NULL) {
		$ref_id = $ref_id ? $ref_id : $_GET['ref_id'];
		$obj_type = ilObject2::_lookupType($ref_id, true);

		return self::getLink($obj_type, $ref_id, $class, $id);
	}


	/**
	 * @param      $obj
	 * @param null $ref_id
	 *
	 * @return string
	 */
	public static function getLinkForObject($obj, $ref_id = NULL) {
		$ref_id = $ref_id ? $ref_id : $_GET['ref_id'];

		return self::getLinkByClass(self::_fromCamelCase(get_class($obj)), $obj->getId(), $ref_id);
	}


	/**
	 * @param string $str
	 *
	 * @return string
	 */
	private static function _fromCamelCase($str) {
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "_" . strtolower($c[1]);');

		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
}

?>
