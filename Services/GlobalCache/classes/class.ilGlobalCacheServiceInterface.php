<?php

/**
 * Class ilGlobalCacheService
 * Base class for all concrete cache implementations.
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.1
 */
interface ilGlobalCacheServiceInterface
{
    /**
     * @param $serialized_value
     * @return mixed
     */
    public function unserialize($serialized_value);
    
    /**
     * @param      $key
     * @return mixed
     */
    public function get($key);
    
    /**
     * @param      $key
     * @param      $serialized_value
     * @param null $ttl
     * @return bool
     */
    public function set($key, $serialized_value, $ttl = null);
    
    /**
     * @return string
     */
    public function getServiceId();
    
    /**
     * @param string $service_id
     */
    public function setServiceId($service_id);
    
    /**
     * @return string
     */
    public function getComponent();
    
    /**
     * @param string $component
     */
    public function setComponent($component);
    
    /**
     * @return bool
     */
    public function isActive();
    
    /**
     * @return bool
     */
    public function isInstallable();
    
    /**
     * @param $key
     * @return string
     */
    public function returnKey($key);
    
    /**
     * @return array
     */
    public function getInfo();
    
    /**
     * @return string
     */
    public function getInstallationFailureReason();
    
    /**
     * @param $key
     * @return bool
     */
    public function exists($key);
    
    /**
     * @param      $key
     * @return bool
     */
    public function delete($key);
    
    /**
     * @param bool $complete
     * @return mixed
     */
    public function flush(bool $complete = false) : bool;
    
    /**
     * @param int $service_type
     */
    public function setServiceType($service_type);
    
    /**
     * @return int
     */
    public function getServiceType();
    
    /**
     * Declare a key as valid. If the key is already known no action is taken.
     * @param string $key The key which should be declared as valid.
     * @return void
     */
    public function setValid($key);
    
    /**
     * Checks whether the cache key is valid or not.
     * @param string $key The key which should be checked.
     * @return bool True if the key is valid otherwise false.
     */
    public function isValid($key);
}
