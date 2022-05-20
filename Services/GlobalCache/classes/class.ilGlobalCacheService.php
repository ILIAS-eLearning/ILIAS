<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
/**
 * Class ilGlobalCacheService
 * Base class for all concrete cache implementations.
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.1
 */
abstract class ilGlobalCacheService implements ilGlobalCacheServiceInterface
{
    protected int $current_time = 0;
    protected array $valid_keys = array();
    protected static array $active = array();
    protected static array $installable = array();
    protected string $service_id = '';
    protected string $component = '';
    protected int $service_type = ilGlobalCache::TYPE_STATIC;
    protected string $valid_key_hash = '';

    /**
     * ilGlobalCacheService constructor.
     */
    public function __construct(string $service_id, string $component)
    {
        $this->setComponent($component);
        $this->setServiceId($service_id);
        self::$active[static::class] = $this->getActive();
        self::$installable[static::class] = ($this->getInstallable() && $this->checkMemory());
    }

    abstract protected function getActive() : bool;

    abstract protected function getInstallable() : bool;

    /**
     * @param mixed $serialized_value
     * @return mixed
     */
    abstract public function unserialize($serialized_value);

    /**
     * @return mixed
     */
    abstract public function get(string $key);

    /**
     * @param mixed $serialized_value
     */
    abstract public function set(string $key, $serialized_value, int $ttl = null) : bool;

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract public function serialize($value);

    public function getServiceId() : string
    {
        return $this->service_id;
    }

    public function setServiceId(string $service_id) : void
    {
        $this->service_id = $service_id;
    }

    public function getComponent() : string
    {
        return $this->component;
    }

    public function setComponent(string $component) : void
    {
        $this->component = $component;
    }

    public function isActive() : bool
    {
        return self::$active[static::class];
    }

    public function isInstallable() : bool
    {
        return self::$installable[static::class];
    }

    public function returnKey(string $key) : string
    {
        return $this->getServiceId() . '_' . $this->getComponent() . '_' . $key;
    }

    public function getInfo() : array
    {
        return array();
    }

    public function getInstallationFailureReason() : string
    {
        if (!$this->getInstallable()) {
            return 'Not installed';
        }
        if (!$this->checkMemory()) {
            return 'Not enough Cache-Memory, set to at least ' . $this->getMinMemory() . 'M';
        }

        return 'Unknown reason';
    }

    protected function getMemoryLimit() : string
    {
        return '9999M';
    }

    protected function getMinMemory() : int
    {
        return 0;
    }

    protected function checkMemory() : bool
    {
        $matches = [];
        $memory_limit = $this->getMemoryLimit();
        if (preg_match('#(\d*)([M|K])#uim', $memory_limit, $matches)) {
            if ($matches[2] === 'M') {
                $memory_limit = $matches[1] * 1024 * 1024;
            } elseif ($matches[2] === 'K') {
                $memory_limit = $matches[1] * 1024;
            }
        } else {
            $memory_limit *= 1024 * 1024; // nnnM -> nnn MB
        }

        return ($memory_limit >= $this->getMinMemory() * 1024 * 1024);
    }

    abstract public function exists(string $key) : bool;

    abstract public function delete(string $key) : bool;

    abstract public function flush(bool $complete = false) : bool;

    public function setServiceType(int $service_type) : void
    {
        $this->service_type = $service_type;
    }

    public function getServiceType() : int
    {
        return $this->service_type;
    }

    public function setValid(string $key) : void
    {
        $this->valid_keys[$key] = true;
    }

    public function isValid(string $key) : bool
    {
        return isset($this->valid_keys[$key]);
    }
}
