<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arFieldCache
 * @version 2.0.7
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arFieldCache
{
    protected static array $cache = array();

    public static function isCached(ActiveRecord $ar) : bool
    {
        return array_key_exists(get_class($ar), self::$cache);
    }

    public static function store(ActiveRecord $ar) : void
    {
        self::$cache[get_class($ar)] = arFieldList::getInstance($ar);
    }

    public static function storeFromStorage(string $storage_class_name, ActiveRecord $foreign_model) : void
    {
        self::$cache[$storage_class_name] = arFieldList::getInstanceFromStorage($foreign_model);
    }

    public static function get(ActiveRecord $ar) : \arFieldList
    {
        if (!self::isCached($ar)) {
            self::store($ar);
        }

        return self::$cache[get_class($ar)];
    }

    public static function purge(ActiveRecord $ar) : void
    {
        unset(self::$cache[get_class($ar)]);
    }

    public static function getPrimaryFieldName(ActiveRecord $ar) : string
    {
        return self::get($ar)->getPrimaryFieldName();
    }

    /**
     * @return mixed
     */
    public static function getPrimaryFieldType(ActiveRecord $ar) : string
    {
        return self::get($ar)->getPrimaryFieldType();
    }
}
