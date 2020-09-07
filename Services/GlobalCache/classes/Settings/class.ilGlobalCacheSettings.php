<?php
require_once('./Services/GlobalCache/classes/class.ilGlobalCache.php');

/**
 * Class ilGlobalCacheSettings
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCacheSettings
{
    const LOG_LEVEL_FORCED = -1;
    const LOG_LEVEL_NONE = 0;
    const LOG_LEVEL_SHY = 1;
    const LOG_LEVEL_NORMAL = 2;
    const LOG_LEVEL_CHATTY = 3;
    const INI_HEADER_CACHE = 'cache';
    const INI_FIELD_ACTIVATE_GLOBAL_CACHE = 'activate_global_cache';
    const INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE = 'global_cache_service_type';
    const INI_HEADER_CACHE_ACTIVATED_COMPONENTS = 'cache_activated_components';
    const INI_FIELD_LOG_LEVEL = 'log_level';
    /**
     * @var int
     */
    protected $service = ilGlobalCache::TYPE_STATIC;
    /**
     * @var array
     */
    protected $activated_components = array();
    /**
     * @var bool
     */
    protected $active = false;
    /**
     * @var int
     */
    protected $log_level = self::LOG_LEVEL_NONE;


    /**
     * @param ilIniFile $ilIniFile
     */
    public function readFromIniFile(ilIniFile $ilIniFile)
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


    /**
     * @param ilIniFile $ilIniFile
     */
    public function writeToIniFile(ilIniFile $ilIniFile)
    {
        $ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_ACTIVATE_GLOBAL_CACHE, $this->isActive() ? '1' : '0');
        $ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_GLOBAL_CACHE_SERVICE_TYPE, $this->getService());
        $ilIniFile->setVariable(self::INI_HEADER_CACHE, self::INI_FIELD_LOG_LEVEL, $this->getLogLevel());

        $ilIniFile->removeGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
        $ilIniFile->addGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
        foreach (ilGlobalCache::getAvailableComponents() as $comp) {
            $ilIniFile->setVariable(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS, $comp, $this->isComponentActivated($comp) ? '1' : '0');
        }
        if ($ilIniFile->write()) {
            ilGlobalCache::log('saved new settings: ' . $this->__toString(), self::LOG_LEVEL_FORCED);
        }
    }


    public function activateAll()
    {
        foreach (ilGlobalCache::getAvailableComponents() as $comp) {
            $this->addActivatedComponent($comp);
        }
    }


    /**
     * @param $component
     */
    public function addActivatedComponent($component)
    {
        $this->activated_components[] = $component;
        $this->activated_components = array_unique($this->activated_components);
    }


    public function resetActivatedComponents()
    {
        $this->activated_components = array();
    }


    /**
     * @param $component
     *
     * @return bool
     */
    public function isComponentActivated($component)
    {
        return in_array($component, $this->activated_components);
    }


    /**
     * @return bool
     */
    public function areAllComponentActivated()
    {
        return count($this->activated_components) == count(ilGlobalCache::getAvailableComponents());
    }


    /**
     * @return int
     */
    public function getService()
    {
        return $this->service;
    }


    /**
     * @param int $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }


    /**
     * @return array
     */
    public function getActivatedComponents()
    {
        return $this->activated_components;
    }


    /**
     * @param array $activated_components
     */
    public function setActivatedComponents($activated_components)
    {
        $this->activated_components = $activated_components;
    }


    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


    /**
     * @param ilIniFile $ilIniFile
     */
    protected function checkIniHeader(ilIniFile $ilIniFile)
    {
        if (!$ilIniFile->readGroup(self::INI_HEADER_CACHE)) {
            $ilIniFile->addGroup(self::INI_HEADER_CACHE);
        }
        if (!$ilIniFile->readGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS)) {
            $ilIniFile->addGroup(self::INI_HEADER_CACHE_ACTIVATED_COMPONENTS);
        }
    }


    /**
     * @return int
     */
    public function getLogLevel()
    {
        return $this->log_level;
    }


    /**
     * @param int $log_level
     */
    public function setLogLevel($log_level)
    {
        $this->log_level = $log_level;
    }


    public function __toString()
    {
        $service = 'Service: ' . ($this->getService() > 0 ? ilGlobalCache::lookupServiceClassName($this->getService()) : 'none');
        $activated = 'Activated Components: ' . implode(', ', $this->getActivatedComponents());
        $log_level = 'Log Level: ' . $this->getLogLevelName();

        return implode("\n", array( '', '', $service, $activated, $log_level, '' ));
    }


    /**
     * @return string
     */
    protected function getLogLevelName()
    {
        return $this->lookupLogLevelName($this->getLogLevel());
    }


    /**
     * @param $level
     *
     * @return string
     */
    protected function lookupLogLevelName($level)
    {
        $r = new ReflectionClass($this);
        foreach ($r->getConstants() as $k => $v) {
            if (strpos($k, 'LOG_LEVEL') === 0 and $v == $level) {
                return $k;
            }
        }

        return '';
    }
}
