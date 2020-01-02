<?php

require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');

/**
 * Class ilShm
 *
 * @beta http://php.net/manual/en/shmop.examples-basic.php
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilShm extends ilGlobalCacheService
{

    /**
     * @var int
     */
    protected static $shm_id = null;
    /**
     * @var int
     */
    protected static $block_size = 0;


    /**
     * @description set self::$active
     */
    protected function getActive()
    {
        self::$active = function_exists('shmop_open');
    }


    /**
     * @description set self::$installable
     */
    protected function getInstallable()
    {
        return false;
        self::$active = function_exists('shmop_open');
    }


    /**
     * @param $service_id
     * @param $component
     */
    public function __construct($service_id, $component)
    {
        parent::__construct($service_id, $component);
        self::$shm_id = shmop_open(0xff3, "c", 0644, 100);
        self::$block_size = shmop_size(self::$shm_id);
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return shm_has_var(self::$shm_id, $key);
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
        return shmop_write(self::$shm_id, $key, $serialized_value);
    }


    /**
     * @param      $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return shmop_read(self::$shm_id, 0, self::$block_size);
    }


    /**
     * @param      $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return shm_remove_var(self::$shm_id, $key);
    }


    /**
     * @return bool
     */
    public function flush()
    {
        shmop_delete(self::$id);

        return true;
    }


    /**
     * @param $value
     *
     * @return mixed
     */
    public function serialize($value)
    {
        return serialize($value);
    }


    /**
     * @param $serialized_value
     *
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return unserialize($serialized_value);
    }
}
