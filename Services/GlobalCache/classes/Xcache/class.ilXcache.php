<?php
require_once('./Services/GlobalCache/classes/class.ilGlobalCacheService.php');

/**
 * Class ilXcache
 *
 * Concrete XCache implementation.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.1
 */
class ilXcache extends ilGlobalCacheService
{
    const MIN_MEMORY = 32;


    /**
     * ilXcache constructor.
     *
     * @param $serviceId
     * @param $component
     */
    public function __construct($serviceId, $component)
    {
        parent::__construct($serviceId, $component);
        $this->readValid();
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return xcache_isset($this->returnKey($key));
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
        return xcache_set($this->returnKey($key), $serialized_value, $ttl);
    }


    /**
     * @param      $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return xcache_get($this->returnKey($key));
    }


    /**
     * @param      $key
     *
     * @return bool
     */
    public function delete($key)
    {
        return xcache_unset($this->returnKey($key));
    }


    /**
     * @param bool $complete
     *
     * @return bool
     */
    public function flush($complete = false)
    {
        $_SERVER["PHP_AUTH_USER"] = "xcache";
        $_SERVER["PHP_AUTH_PW"] = "xcache";

        xcache_clear_cache(XC_TYPE_VAR, 0);

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


    /**
     * @return bool
     */
    protected function getActive()
    {
        $function_exists = function_exists('xcache_set');
        $var_size = ini_get('xcache.var_size') != '0M';
        $var_count = ini_get('xcache.var_count') > 0;
        $api = (php_sapi_name() !== 'cli');

        $active = $function_exists and $var_size and $var_count and $api;

        return $active;
    }


    /**
     * @return bool
     */
    protected function getInstallable()
    {
        return function_exists('xcache_set');
    }


    /**
     * @return array
     */
    public function getInfo()
    {
        if ($this->isActive()) {
            return xcache_info(XC_TYPE_VAR, 0);
        }
    }


    /**
     * @return int|string
     */
    protected function getMemoryLimit()
    {
        return ini_get('xcache.var_size');
    }


    /**
     * @return int
     */
    protected function getMinMemory()
    {
        return self::MIN_MEMORY;
    }

    /**
     * @return void
     *
     * @description save self::$valid_keys to GlobalCache
     */
    protected function saveValid()
    {
        if ($this->isActive()) {
            if ($this->valid_key_hash != md5(serialize($this->valid_keys))) {
                $this->set('valid_keys', $this->serialize($this->valid_keys));
            }
        }
    }


    /**
     * @return void
     *
     * @description set self::$valid_keys from GlobalCache
     */
    protected function readValid()
    {
        if ($this->isActive() && $this->isInstallable()) {
            $this->valid_keys = $this->unserialize($this->get('valid_keys'));
            $this->valid_key_hash = md5(serialize($this->valid_keys));
        }
    }

    public function __destruct()
    {
        $this->saveValid();
    }
}
