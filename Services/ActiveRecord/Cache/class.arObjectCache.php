<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class arObjectCache
 * @version 2.0.7
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arObjectCache
{
    protected static array $cache = array();

    /**
     * @param $class
     * @param $id
     */
    public static function isCached($class, $id): bool
    {
        if (!isset(self::$cache[$class])) {
            return false;
        }
        if (!isset(self::$cache[$class][$id]) || !self::$cache[$class][$id] instanceof ActiveRecord) {
            return false;
        }

        return array_key_exists($id, self::$cache[$class]);
    }

    public static function store(ActiveRecord $object): void
    {
        if (!isset($object->is_new)) {
            self::$cache[get_class($object)][$object->getPrimaryFieldValue()] = $object;
        }
    }

    public static function printStats(): void
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
     * @throws arException
     */
    public static function get($class, $id): \ActiveRecord
    {
        $instance = new $class();
        if (!self::isCached($class, $id)) {
            throw new arException(arException::GET_UNCACHED_OBJECT, $class . ': ' . $id);
        }

        return self::$cache[$class][$id];
    }

    public static function purge(ActiveRecord $object): void
    {
        unset(self::$cache[get_class($object)][$object->getPrimaryFieldValue()]);
    }

    /**
     * @param $class_name
     */
    public static function flush($class_name): void
    {
        if ($class_name instanceof ActiveRecord) {
            $class_name = get_class($class_name);
        }
        unset(self::$cache[$class_name]);
    }
}
