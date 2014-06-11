<?php
require_once(dirname(__FILE__) . '/../Exception/class.arException.php');

/**
 * Class arObjectCache
 *
 * @version 2.0.4
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
		if (! self::$cache[$class][$id] instanceof ActiveRecord) {
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
	 * @throws arException
	 * @return ActiveRecord
	 */
	public static function get($class, $id) {
		if (! self::isCached($class, $id)) {
			throw new arException(arException::GET_UNCACHED_OBJECT, $class . ': ' . $id);
		}

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
