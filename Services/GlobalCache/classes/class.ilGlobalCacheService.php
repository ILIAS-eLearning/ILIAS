<?php

/**
 * Class ilGlobalCacheService
 * Base class for all concrete cache implementations.
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.1
 */
abstract class ilGlobalCacheService implements ilGlobalCacheServiceInterface
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
    
    public function __construct($service_id, $component)
    {
        $this->setComponent($component);
        $this->setServiceId($service_id);
        self::$active[get_called_class()] = $this->getActive();
        self::$installable[get_called_class()] = ($this->getInstallable() and $this->checkMemory());
    }
    
    abstract protected function getActive();
    
    abstract protected function getInstallable();
    
    abstract public function unserialize($serialized_value);
    
    abstract public function get($key);
    
    abstract public function set($key, $serialized_value, $ttl = null);
    
    abstract public function serialize($value);
    
    public function getServiceId()
    {
        return $this->service_id;
    }
    
    public function setServiceId($service_id)
    {
        $this->service_id = $service_id;
    }
    
    public function getComponent()
    {
        return $this->component;
    }
    
    public function setComponent($component)
    {
        $this->component = $component;
    }
    
    public function isActive()
    {
        return self::$active[get_called_class()];
    }
    
    public function isInstallable()
    {
        return self::$installable[get_called_class()];
    }
    
    public function returnKey($key)
    {
        return $str = $this->getServiceId() . '_' . $this->getComponent() . '_' . $key;
    }
    
    public function getInfo()
    {
        return array();
    }
    
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
    
    protected function getMemoryLimit()
    {
        return 9999;
    }
    
    protected function getMinMemory()
    {
        return 0;
    }
    
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
    
    abstract public function exists($key);
    
    abstract public function delete($key);
    
    abstract public function flush(bool $complete = false) : bool;
    
    public function setServiceType($service_type)
    {
        $this->service_type = $service_type;
    }
    
    public function getServiceType()
    {
        return $this->service_type;
    }
    
    public function setValid($key)
    {
        $this->valid_keys[$key] = true;
    }
    
    public function isValid($key)
    {
        return isset($this->valid_keys[$key]);
    }
}
