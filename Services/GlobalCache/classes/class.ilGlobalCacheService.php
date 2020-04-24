<?php

/**
 * Class ilGlobalCacheService
 *
 * Base class for all concrete cache implementations.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.1
 */
abstract class ilGlobalCacheService
{

    /**
     * @var int
     */
    protected $current_time = 0;
    /**
     * @var array
     */
    protected $valid_keys = array();
    /**
     * @var bool
     */
    protected static $active = array();
    /**
     * @var bool
     */
    protected static $installable = array();
    /**
     * @var string
     */
    protected $service_id = '';
    /**
     * @var string
     */
    protected $component = '';
    /**
     * @var int
     */
    protected $service_type = ilGlobalCache::TYPE_STATIC;
    /**
     * @var string
     */
    protected $valid_key_hash = '';


    /**
     * @param $service_id
     * @param $component
     */
    public function __construct($service_id, $component)
    {
        $this->setComponent($component);
        $this->setServiceId($service_id);
        self::$active[get_called_class()] = $this->getActive();
        self::$installable[get_called_class()] = ($this->getInstallable() and $this->checkMemory());
    }


    /**
     * @return bool
     */
    abstract protected function getActive();


    /**
     * @return bool
     */
    abstract protected function getInstallable();


    /**
     * @param $serialized_value
     *
     * @return mixed
     */
    abstract public function unserialize($serialized_value);


    /**
     * @param      $key
     *
     * @return mixed
     */
    abstract public function get($key);


    /**
     * @param      $key
     * @param      $serialized_value
     * @param null $ttl
     *
     * @return bool
     */
    abstract public function set($key, $serialized_value, $ttl = null);


    /**
     * @param $value
     *
     * @return mixed
     */
    abstract public function serialize($value);


    /**
     * @return string
     */
    public function getServiceId()
    {
        return $this->service_id;
    }


    /**
     * @param string $service_id
     */
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;
    }


    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }


    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }


    /**
     * @return bool
     */
    public function isActive()
    {
        return self::$active[get_called_class()];
    }


    /**
     * @return bool
     */
    public function isInstallable()
    {
        return self::$installable[get_called_class()];
    }


    /**
     * @param $key
     *
     * @return string
     */
    public function returnKey($key)
    {
        return $str = $this->getServiceId() . '_' . $this->getComponent() . '_' . $key;
    }


    /**
     * @return array
     */
    public function getInfo()
    {
        return array();
    }


    /**
     * @return string
     */
    public function getInstallationFailureReason()
    {
        if (!$this->getInstallable()) {
            return 'Not installed';
        }
        if (!$this->checkMemory()) {
            return 'Not enough Cache-Memory, set to at least ' . $this->getMinMemory() . 'M';
        }

        return 'Unknown reason';
    }


    /**
     * @return int
     */
    protected function getMemoryLimit()
    {
        return 9999;
    }


    /**
     * @return int
     */
    protected function getMinMemory()
    {
        return 0;
    }


    /**
     * @return bool
     */
    protected function checkMemory()
    {
        $matches = array();
        $memory_limit = $this->getMemoryLimit();
        if (preg_match('/([0-9]*)([M|K])/uism', $memory_limit, $matches)) {
            switch ($matches[2]) {
                case 'M':
                    $memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
                    break;
                case 'K':
                    $memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
                    break;
            }
        } else {
            $memory_limit = $memory_limit * 1024 * 1024; // nnnM -> nnn MB
        }

        return ($memory_limit >= $this->getMinMemory() * 1024 * 1024);
    }


    /**
     * @param $key
     *
     * @return bool
     */
    abstract public function exists($key);


    /**
     * @param      $key
     *
     * @return bool
     */
    abstract public function delete($key);


    /**
     * @param bool $complete
     *
     * @return mixed
     */
    abstract public function flush($complete = false);


    /**
     * @param int $service_type
     */
    public function setServiceType($service_type)
    {
        $this->service_type = $service_type;
    }


    /**
     * @return int
     */
    public function getServiceType()
    {
        return $this->service_type;
    }


    /**
     * Declare a key as valid. If the key is already known no action is taken.
     *
     * This method exists only for legacy reasons and has only a real function
     * in combination with XCache.
     *
     * @param string $key The key which should be declared as valid.
     *
     * @return void
     */
    public function setValid($key)
    {
        $this->valid_keys[$key] = true;
    }

    /**
     * Set the key as invalid.
     * This method will invalidate all keys if no argument is given or null.
     *
     * This method exists only for legacy reasons and has only a real function
     * in combination with XCache.
     *
     * @param string $key   The key which should be invalidated or null to invalidate all.
     *
     * @return void
     */
    public function setInvalid($key = null)
    {
        if ($key !== null) {
            unset($this->valid_keys[$key]);
        } else {
            unset($this->valid_keys);
        }
    }


    /**
     * Checks whether the cache key is valid or not.
     *
     * This method exists only for legacy reasons and has only a real function
     * in combination with XCache.
     *
     * @param string $key   The key which should be checked.
     *
     * @return bool True if the key is valid otherwise false.
     */
    public function isValid($key)
    {
        return isset($this->valid_keys[$key]);
    }
}
