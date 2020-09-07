<?php
require_once(dirname(__FILE__) . '/../Exception/class.arException.php');

/**
 * Class arObjectCache
 *
 * @version 2.0.7
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arObjectCache
{

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
    public static function isCached($class, $id)
    {
        $instance = new $class();
        if ($instance instanceof CachedActiveRecord && $instance->getCacheIdentifier() != '') {
            if ($instance->getCache()->exists($instance->getCacheIdentifier())) {
                return true;
            }
        }

        if (!isset(self::$cache[$class])) {
            return false;
        }
        if (!isset(self::$cache[$class][$id]) || !self::$cache[$class][$id] instanceof ActiveRecord) {
            return false;
        }

        return in_array($id, array_keys(self::$cache[$class]));
    }


    /**
     * @param ActiveRecord $object
     */
    public static function store(ActiveRecord $object)
    {
        if ($object instanceof CachedActiveRecord && $object->getCacheIdentifier() != '') {
            if ($object->getCache()->set($object->getCacheIdentifier(), $object, $object->getTTL())) {
                return;
            }
        }
        if (!isset($object->is_new)) {
            self::$cache[get_class($object)][$object->getPrimaryFieldValue()] = $object;
        }
    }


    public static function printStats()
    {
        foreach (self::$cache as $class => $objects) {
            echo $class;
            echo ": ";
            echo count($objects);
            echo " Objects<br>";
        }
    }


    /**
     * @param $class
     * @param $id
     *
     * @throws arException
     * @return ActiveRecord
     */
    public static function get($class, $id)
    {
        $instance = new $class();
        if ($instance instanceof CachedActiveRecord && $instance->getCacheIdentifier() != '') {
            if ($instance->getCache()->exists($instance->getCacheIdentifier())) {
                return $instance->getCache()->get($instance->getCacheIdentifier());
            }
        }
        if (!self::isCached($class, $id)) {
            throw new arException(arException::GET_UNCACHED_OBJECT, $class . ': ' . $id);
        }

        return self::$cache[$class][$id];
    }


    /**
     * @param ActiveRecord $object
     */
    public static function purge(ActiveRecord $object)
    {
        if ($object instanceof CachedActiveRecord && $object->getCacheIdentifier() != '') {
            $object->getCache()->delete($object->getCacheIdentifier());
        }
        unset(self::$cache[get_class($object)][$object->getPrimaryFieldValue()]);
    }


    /**
     * @param $class_name
     */
    public static function flush($class_name)
    {
        $instance = new $class_name();
        if ($instance instanceof CachedActiveRecord && $instance->getCacheIdentifier() != '') {
            $instance->getCache()->flush();
        }

        if ($class_name instanceof ActiveRecord) {
            $class_name = get_class($class_name);
        }
        unset(self::$cache[$class_name]);
    }
}
