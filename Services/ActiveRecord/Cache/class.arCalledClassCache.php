<?php

/**
 * Class arCalledClassCache
 * @version 2.0.7
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arCalledClassCache
{

    protected static array $cache = array();

    public static function isCached(string $class_name) : bool
    {
        return array_key_exists($class_name, self::$cache);
    }

    public static function store(string $class_name) : void
    {
        self::$cache[$class_name] = arFactory::getInstance($class_name, null);
    }

    public static function get(string $class_name) : ActiveRecord
    {
        if (!self::isCached($class_name)) {
            self::store($class_name);
        }

        return self::$cache[$class_name];
    }

    public static function purge(string $class_name) : void
    {
        unset(self::$cache[$class_name]);
    }
}
