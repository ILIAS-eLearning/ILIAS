<?php

/**
 * Class arCalledClassCache
 *
 * @version 2.0.7
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arCalledClassCache {

	/**
	 * @var array
	 */
	protected static $cache = array();


	/**
	 * @param $class_name
	 *
	 * @return bool
	 */
	public static function isCached($class_name) {
		return in_array($class_name, array_keys(self::$cache));
	}


	/**
	 * @param $class_name
	 */
	public static function store($class_name) {
		self::$cache[$class_name] = arFactory::getInstance($class_name, NULL);
	}


	/**
	 * @param $class_name
	 *
	 * @return mixed
	 */
	public static function get($class_name) {
		if (!self::isCached($class_name)) {
			self::store($class_name);
		}

		return self::$cache[$class_name];
	}


	/**
	 * @param $class_name
	 */
	public static function purge($class_name) {
		unset(self::$cache[$class_name]);
	}
}

?>
