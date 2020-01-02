<?php
require_once('./Services/AuthShibboleth/classes/Config/class.shibConfig.php');

/**
 * Class shibServerData
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class shibServerData extends shibConfig
{

    /**
     * @var bool
     */
    protected static $cache = null;


    /**
     * @param array $data
     */
    protected function __construct($data)
    {
        $shibConfig = shibConfig::getInstance();
        foreach (array_keys(get_class_vars('shibConfig')) as $field) {
            $str = $shibConfig->getValueByKey($field);
            if ($str !== null) {
                $this->{$field} = $data[$str];
            }
        }
    }


    /**
     * @return bool|\shibServerData
     */
    public static function getInstance()
    {
        if (!isset(self::$cache)) {
            self::$cache = new self($_SERVER);
        }

        return self::$cache;
    }
}
