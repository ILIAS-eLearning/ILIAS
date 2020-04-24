<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');

/**
 * Class ilStaticCache
 *
 * @beta
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilStaticCache extends ilGlobalCacheService
{

    /**
     * @return bool
     */
    protected function getActive()
    {
        return true;
    }


    /**
     * @return bool
     */
    protected function getInstallable()
    {
        return true;
    }


    /**
     * @var array
     */
    protected static $cache = array();


    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset(self::$cache[$this->getComponent()][$key]);
    }


    /**
     * @param      $key
     * @param      $serialized_value
     * @param null $ttl
     *
     * @return bool
     */
    public function set($key, $serialized_value, $ttl = null)
    {
        return self::$cache[$this->getComponent()][$key] = $serialized_value;
    }


    /**
     * @param      $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return self::$cache[$this->getComponent()][$key];
    }


    /**
     * @param      $key
     *
     * @return bool
     */
    public function delete($key)
    {
        unset(self::$cache[$this->getComponent()][$key]);
    }

    /**
     * @param bool $complete
     * @return bool
     */
    public function flush($complete = false)
    {
        if ($complete) {
            self::$cache = array();
        } else {
            unset(self::$cache[$this->getComponent()]);
        }

        return true;
    }


    /**
     * @param $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        return ($value);
    }


    /**
     * @param $serialized_value
     *
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return ($serialized_value);
    }
}
