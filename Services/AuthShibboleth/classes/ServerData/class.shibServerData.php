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
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class shibServerData
 * @deprecated
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibServerData extends shibConfig
{
    protected static ?shibServerData $server_cache = null;

    protected function __construct(array $data)
    {
        $shibConfig = shibConfig::getInstance();
        foreach (array_keys(get_class_vars('shibConfig')) as $field) {
            $str = $shibConfig->getValueByKey($field);
            if ($str !== null) {
                $this->{$field} = $data[$str] ?? '';
            }
        }
    }

    public static function getInstance() : shibServerData
    {
        if (!isset(self::$server_cache)) {
            self::$server_cache = new self($_SERVER);
        }

        return self::$server_cache;
    }
}
