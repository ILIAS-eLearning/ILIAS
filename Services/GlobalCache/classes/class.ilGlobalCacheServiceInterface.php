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
     * @return mixed
     */
    public function get(string $key);
    
    /**
     * @param string   $key
     * @param          $serialized_value
     * @param int|null $ttl
     * @return bool
     */
    public function set(string $key, $serialized_value, int $ttl = null) : bool;
    
    public function getServiceId() : int;
    
    public function setServiceId(int $service_id) : void;
    
    public function getComponent() : string;
    
    public function setComponent(string $component) : void;
    
    public function isActive() : bool;
    
    public function isInstallable() : bool;
    
    public function returnKey(string $key) : string;
    
    public function getInfo() : array;
    
    public function getInstallationFailureReason() : string;
    
    public function exists(string $key) : bool;
    
    public function delete(string $key) : bool;
    
    /**
     * @return mixed
     */
    public function flush(bool $complete = false) : bool;
    
    public function setServiceType(int $service_type);
    
    /**
     * @return int
     */
    public function getServiceType();
    
    /**
     * Declare a key as valid. If the key is already known no action is taken.
     * @param string $key The key which should be declared as valid.
     * @return void
     */
    public function setValid(string $key);
    
    /**
     * Checks whether the cache key is valid or not.
     * @param string $key The key which should be checked.
     * @return bool True if the key is valid otherwise false.
     */
    public function isValid(string $key) : bool;
}
