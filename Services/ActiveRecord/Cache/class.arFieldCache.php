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
 * Class arFieldCache
 * @version 2.0.7
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class arFieldCache
{
    protected static array $cache = [];

    public static function isCached(ActiveRecord $activeRecord): bool
    {
        return array_key_exists($activeRecord::class, self::$cache);
    }

    public static function store(ActiveRecord $activeRecord): void
    {
        self::$cache[$activeRecord::class] = arFieldList::getInstance($activeRecord);
    }

    public static function storeFromStorage(string $storage_class_name, ActiveRecord $activeRecord): void
    {
        self::$cache[$storage_class_name] = arFieldList::getInstanceFromStorage($activeRecord);
    }

    public static function get(ActiveRecord $activeRecord): \arFieldList
    {
        if (!self::isCached($activeRecord)) {
            self::store($activeRecord);
        }

        return self::$cache[$activeRecord::class];
    }

    public static function purge(ActiveRecord $activeRecord): void
    {
        unset(self::$cache[$activeRecord::class]);
    }

    public static function getPrimaryFieldName(ActiveRecord $activeRecord): string
    {
        return self::get($activeRecord)->getPrimaryFieldName();
    }

    /**
     * @return mixed
     */
    public static function getPrimaryFieldType(ActiveRecord $activeRecord): string
    {
        return self::get($activeRecord)->getPrimaryFieldType();
    }
}
