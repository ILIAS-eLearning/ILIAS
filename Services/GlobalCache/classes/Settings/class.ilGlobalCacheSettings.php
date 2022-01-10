<?php

use ILIAS\Setup;

/**
 * Class ilGlobalCacheSettings
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheSettings implements Setup\Config
{
    /**
     * @var int
     */
    const LOG_LEVEL_FORCED = -1;
    /**
     * @var int
     */
    const LOG_LEVEL_NONE = 0;
    /**
     * @var int
     */
    const LOG_LEVEL_SHY = 1;
    /**
     * @var int
     */
    const LOG_LEVEL_NORMAL = 2;
    /**
     * @var int
     */
    const LOG_LEVEL_CHATTY = 3;
    /**
     * @var string
     */
    const INI_HEADER_CACHE = 'cache';
    /**
     * @var string
     */
    const INI_FIELD_ACTIVATE_GLOBAL_CACHE = 'activate_global_cache';
    /**
     * @var string
     */
    const INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE = 'global_cache_service_type';
    /**
     * @var string
     */
    const INI_HEADER_CACHE_ACTIVATED_COMPONENTS = 'cache_activated_components';
    /**
     * @var string
     */
    const INI_FIELD_LOG_LEVEL = 'log_level';
    protected int $service = ilGlobalCache::TYPE_STATIC;
    protected array $activated_components = array();
    protected bool $active = false;
    protected int $log_level = self::LOG_LEVEL_NONE;
    /**
     * @var ilMemcacheServer[]
     */
    protected array $memcached_nodes = [];

    public function readFromIniFile(ilIniFile $ilIniFile) : void
    {
        $this->checkIniHeader($ilIniFile);
        $this->setActive($ilIniFile->readVariable(self::INI_HEADER_CACHE, self::INI_FIELD_ACTIVATE_GLOBAL_CACHE));
        $this->setService($ilIniFile->readVariable(self::INI_HEADER_CACHE, self::INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE));
        $this->setLogLevel($ilIniFile->readVariable(self::INI_HEADER_CACHE, self::INI_FIELD_LOG_LEVEL));
        if (!$this->isActive()) {
            $this->resetActivatedComponents();
        } else {
            $cache_components = $ilIniFile->readGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
            if (is_array($cache_components)) {
                foreach ($cache_components as $comp => $v) {
                    if ($v) {
                        $this->addActivatedComponent($comp);
                    }
                }
            }
        }
    }

    public function writeToIniFile(ilIniFile $ilIniFile) : void
    {
        $ilIniFile->setVariable(
            self::INI_HEADER_CACHE,
            self::INI_FIELD_ACTIVATE_GLOBAL_CACHE,
            $this->isActive() ? '1' : '0'
        );
        $ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE, $this->getService());
        $ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_LOG_LEVEL, $this->getLogLevel());

        $ilIniFile->removeGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
        $ilIniFile->addGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
        foreach (ilGlobalCache::getAvailableComponents() as $comp) {
            $ilIniFile->setVariable(
                self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS,
                $comp,
                $this->isComponentActivated($comp) ? '1' : '0'
            );
        }
        if ($ilIniFile->write()) {
            ilGlobalCache::log('saved new settings: ' . $this->__toString(), self::LOG_LEVEL_FORCED);
        }
    }

    public function activateAll() : void
    {
        foreach (ilGlobalCache::getAvailableComponents() as $comp) {
            $this->addActivatedComponent($comp);
        }
    }

    /**
     * @param mixed $component
     */
    public function addActivatedComponent($component) : void
    {
        $this->activated_components[] = $component;
        $this->activated_components = array_unique($this->activated_components);
    }

    public function resetActivatedComponents() : void
    {
        $this->activated_components = array();
    }

    /**
     * @param mixed $component
     */
    public function isComponentActivated($component) : bool
    {
        return in_array($component, $this->activated_components);
    }

    public function areAllComponentActivated() : bool
    {
        return count($this->activated_components) === count(ilGlobalCache::getAvailableComponents());
    }

    public function getService() : int
    {
        return $this->service;
    }

    public function setService(int $service) : void
    {
        $this->service = $service;
    }

    public function getActivatedComponents() : array
    {
        return $this->activated_components;
    }

    public function setActivatedComponents(array $activated_components) : void
    {
        $this->activated_components = $activated_components;
    }

    public function isActive() : bool
    {
        return $this->active;
    }

    public function setActive(bool $active) : void
    {
        $this->active = $active;
    }

    protected function checkIniHeader(ilIniFile $ilIniFile) : void
    {
        if (!$ilIniFile->readGroup(self::INI_HEADER_CACHE)) {
            $ilIniFile->addGroup(self::INI_HEADER_CACHE);
        }
        if (!$ilIniFile->readGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS)) {
            $ilIniFile->addGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
        }
    }

    public function getLogLevel() : int
    {
        return $this->log_level;
    }

    public function setLogLevel(int $log_level) : void
    {
        $this->log_level = $log_level;
    }

    public function __toString() : string
    {
        $service = 'Service: ' . ($this->getService() > 0 ? ilGlobalCache::lookupServiceClassName($this->getService()) : 'none');
        $activated = 'Activated Components: ' . implode(', ', $this->getActivatedComponents());
        $log_level = 'Log Level: ' . $this->getLogLevelName();

        return implode("\n", array('', '', $service, $activated, $log_level, ''));
    }

    protected function getLogLevelName() : string
    {
        return $this->lookupLogLevelName($this->getLogLevel());
    }

    protected function lookupLogLevelName(int $level) : string
    {
        $r = new ReflectionClass($this);
        foreach ($r->getConstants() as $k => $v) {
            if (strpos($k, 'LOG_LEVEL') === 0 && $v == $level) {
                return $k;
            }
        }

        return '';
    }

    public function addMemcachedNode(ilMemcacheServer $node_id) : void
    {
        $this->memcached_nodes[] = $node_id;
    }

    public function getMemcachedNodes() : array
    {
        return $this->memcached_nodes;
    }
}
