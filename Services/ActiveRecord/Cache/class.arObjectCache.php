<?php

/**
 * Class arObjectCache
 *
 * @version 1.0.0
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arObjectCache {

	/**
	 * @var array
	 */
	protected static $cache = array();


	/**
	 * @param $class
	 * @param $id
	 *
	 * @return bool
	 */
	public static function isCached($class, $id) {
		if (! isset(self::$cache[$class])) {
			return false;
		}

		return in_array($id, array_keys(self::$cache[$class]));
	}


	/**
	 * @param ActiveRecord $object
	 */
	public static function store(ActiveRecord $object) {
		self::$cache[get_class($object)][$object->getPrimaryFieldValue()] = $object;
	}


	/**
	 * @param $class
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function get($class, $id) {
		return self::$cache[$class][$id];
	}


	/**
	 * @param ActiveRecord $object
	 */
	public static function purge(ActiveRecord $object) {
		unset(self::$cache[get_class($object)][$object->getPrimaryFieldValue()]);
	}
}

?>
