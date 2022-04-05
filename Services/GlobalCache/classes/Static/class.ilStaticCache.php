<?php

/**
 * Class ilStaticCache
 * @beta
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilStaticCache extends ilGlobalCacheService
{
    protected function getActive() : bool
    {
        return true;
    }

    protected function getInstallable() : bool
    {
        return true;
    }

    protected static array $cache = array();

    public function exists(string $key) : bool
    {
        return isset(self::$cache[$this->getComponent()][$key]);
    }

    /**
     * @param mixed $serialized_value
     */
    public function set(string $key, $serialized_value, int $ttl = null) : bool
    {
        return self::$cache[$this->getComponent()][$key] = $serialized_value;
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return self::$cache[$this->getComponent()][$key];
    }

    public function delete(string $key) : bool
    {
        unset(self::$cache[$this->getComponent()][$key]);

        return true;
    }

    public function flush(bool $complete = false) : bool
    {
        if ($complete) {
            self::$cache = array();
        } else {
            unset(self::$cache[$this->getComponent()]);
        }

        return true;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value)
    {
        return ($value);
    }

    /**
     * @param mixed $serialized_value
     * @return mixed
     */
    public function unserialize($serialized_value)
    {
        return ($serialized_value);
    }
}
