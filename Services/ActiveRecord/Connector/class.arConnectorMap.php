<?php

/**
 * Class arConnectorMap
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class arConnectorMap
{

    /**
     * @var array
     */
    protected static $map = array();


    /**
     * @param ActiveRecord $ar
     * @param arConnector  $connector
     */
    public static function register(ActiveRecord $ar, arConnector $connector)
    {
        self::$map[get_class($ar)] = $connector;
    }


    /**
     * @param ActiveRecord $ar
     *
     * @return arConnector
     */
    public static function get(ActiveRecord $ar)
    {
        if (self::$map[get_class($ar)] instanceof arConnector) {
            return self::$map[get_class($ar)];
        }

        return new arConnectorDB();
    }
}
