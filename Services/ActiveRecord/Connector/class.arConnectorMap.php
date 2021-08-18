<?php

/**
 * Class arConnectorMap
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class arConnectorMap
{

    protected static array $map = array();

    public static function register(ActiveRecord $ar, arConnector $connector) : void
    {
        self::$map[get_class($ar)] = $connector;
    }

    public static function get(ActiveRecord $ar) : \arConnector
    {
        if (isset(self::$map[get_class($ar)]) && self::$map[get_class($ar)] instanceof arConnector) {
            return self::$map[get_class($ar)];
        }

        return new arConnectorDB();
    }
}
