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
 * Class arConnectorMap
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class arConnectorMap
{
    protected static array $map = array();

    public static function register(ActiveRecord $ar, arConnector $connector): void
    {
        self::$map[get_class($ar)] = $connector;
    }

    public static function get(ActiveRecord $ar): \arConnector
    {
        if (isset(self::$map[get_class($ar)]) && self::$map[get_class($ar)] instanceof arConnector) {
            return self::$map[get_class($ar)];
        }

        return new arConnectorDB();
    }
}
