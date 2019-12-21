<?php
require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcache.php');
require_once('./Services/GlobalCache/classes/Xcache/class.ilXcache.php');
require_once('./Services/GlobalCache/classes/Apc/class.ilApc.php');
require_once('./Services/GlobalCache/classes/Static/class.ilStaticCache.php');
require_once('Settings/class.ilGlobalCacheSettings.php');

/**
 * Class ilGlobalCache
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCache
{
    const MSG = 'Global Cache not active, can not access cache';
    const ACTIVE = true;
    const TYPE_STATIC = 0;
    const TYPE_XCACHE = 1;
    const TYPE_MEMCACHED = 2;
    const TYPE_APC = 3;
    const TYPE_FALLBACK = self::TYPE_STATIC;
    const COMP_CLNG = 'clng';
    const COMP_OBJ_DEF = 'obj_def';
    const COMP_TEMPLATE = 'tpl';
    const COMP_ILCTRL = 'ilctrl';
    const COMP_PLUGINS = 'plugins';
    const COMP_COMPONENT = 'comp';
    const COMP_RBAC_UA = 'rbac_ua';
    const COMP_EVENTS = 'events';
    const COMP_TPL_BLOCKS = 'tpl_blocks';
    const COMP_TPL_VARIABLES = 'tpl_variables';
    const COMP_GLOBAL_SCREEN = 'global_screen';
    /**
     * @var array
     */
    protected static $types = array(
        self::TYPE_MEMCACHED,
        self::TYPE_XCACHE,
        self::TYPE_APC,
        self::TYPE_STATIC,
    );
    /**
     * @var array
     */
    protected static $available_types = array(
        self::TYPE_MEMCACHED,
        self::TYPE_XCACHE,
        self::TYPE_APC,
        self::TYPE_STATIC,
    );
    /**
     * @var array
     */
    protected static $active_components = array();
    /**
     * @var array
     */
    protected static $available_components = array(
        self::COMP_CLNG,
        self::COMP_OBJ_DEF,
        self::COMP_ILCTRL,
        self::COMP_COMPONENT,
        self::COMP_TEMPLATE,
        self::COMP_TPL_BLOCKS,
        self::COMP_TPL_VARIABLES,
        self::COMP_EVENTS,
        self::COMP_GLOBAL_SCREEN,
    );
    /**
     * @var array
     */
    protected static $type_per_component = array();
    /**
     * @var string
     */
    protected static $unique_service_id = null;
    /**
     * @var ilGlobalCache
     */
    protected static $instances;
    /**
     * @var ilGlobalCacheService
     */
    protected $global_cache;
    /**
     * @var string
     */
    protected $component;
    /**
     * @var bool
     */
    protected $active = true;
    /**
     * @var int
     */
    protected $service_type = ilGlobalCache::TYPE_STATIC;
    /**
     * @var ilGlobalCacheSettings
     */
    protected static $settings;


    /**
     * @param ilGlobalCacheSettings $ilGlobalCacheSettings
     */
    public static function setup(ilGlobalCacheSettings $ilGlobalCacheSettings)
    {
        self::setSettings($ilGlobalCacheSettings);
        self::setActiveComponents($ilGlobalCacheSettings->getActivatedComponents());
    }


    /**
     * @param null $component
     *
     * @return ilGlobalCache
     */
    public static function getInstance($component)
    {
        if (!isset(self::$instances[$component])) {
            $service_type = self::getSettings()->getService();
            $ilGlobalCache = new self($service_type);
            $ilGlobalCache->setComponent($component);
            $ilGlobalCache->initCachingService();

            self::$instances[$component] = $ilGlobalCache;
        }

        return self::$instances[$component];
    }


    /**
     * @param $service_type
     */
    protected function __construct($service_type)
    {
        $this->checkSettings();
        self::generateServiceId();
        $this->setServiceType($service_type);
    }


    protected function initCachingService()
    {
        /**
         * @var $ilGlobalCacheService ilGlobalCacheService
         */
        if (!$this->getComponent()) {
            $this->setComponent('default');
        }
        $serviceName = self::lookupServiceClassName($this->getServiceType());
        $ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
        $ilGlobalCacheService->setServiceType($this->getServiceType());
        $this->global_cache = $ilGlobalCacheService;
        $this->setActive(in_array($this->getComponent(), self::getActiveComponents()));
    }


    protected function checkSettings()
    {
        if (!$this->getSettings() instanceof ilGlobalCacheSettings) {
            $ilGlobalCacheSettings = new ilGlobalCacheSettings();
            $this->setSettings($ilGlobalCacheSettings);
        }
    }


    /**
     * @param $message
     */
    public static function log($message, $log_level)
    {
        if ($log_level <= self::getSettings()->getLogLevel()) {
            global $DIC;
            $ilLog = $DIC['ilLog'];
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $function = $backtrace[1]['function'];
            $class = $backtrace[1]['class'];
            if ($ilLog instanceof ilComponentLogger) {
                $ilLog->alert($class . '::' . $function . '(): ' . $message);
            }
        }
    }


    /**
     * @return string
     */
    protected static function generateServiceId()
    {
        if (!isset(self::$unique_service_id)) {
            $rawServiceId = '_';
            if (defined('CLIENT_ID')) {
                $rawServiceId .= 'il_' . CLIENT_ID;
            }
            self::$unique_service_id = substr(md5($rawServiceId), 0, 6);
        }
    }


    public static function flushAll()
    {
        self::log('requested...', ilGlobalCacheSettings::LOG_LEVEL_NORMAL);
        /**
         * @var $service  ilApc
         */
        foreach (self::$types as $type) {
            $serviceName = self::lookupServiceClassName($type);
            $service = new $serviceName(self::generateServiceId(), 'flush');
            if ($service->isActive()) {
                self::log('Told ' . $serviceName . ' to flush', ilGlobalCacheSettings::LOG_LEVEL_NORMAL);
                $returned = $service->flush();
                self::log($serviceName . ' returned status ' . ($returned ? 'ok' : 'failure'), ilGlobalCacheSettings::LOG_LEVEL_NORMAL);
            }
        }
    }


    /**
     * @return ilGlobalCache[]
     */
    public static function getAllInstallableTypes()
    {
        $types = array();
        foreach (self::getAllTypes() as $type) {
            if ($type->isCacheServiceInstallable()) {
                $types[] = $type;
            }
        }

        return $types;
    }


    /**
     * @param bool $only_available
     * @return array
     */
    public static function getAllTypes($only_available = true)
    {
        $types = array();
        foreach (self::$types as $type) {
            if ($only_available && !in_array($type, self::$available_types)) {
                continue;
            }
            $obj = new self($type);
            $obj->initCachingService();
            $types[$type] = $obj;
        }

        return $types;
    }


    /**
     * @param $service_type
     *
     * @return string
     */
    public static function lookupServiceClassName($service_type)
    {
        switch ($service_type) {
            case self::TYPE_APC:
                return 'ilApc';
                break;
            case self::TYPE_MEMCACHED:
                return 'ilMemcache';
                break;
            case self::TYPE_XCACHE:
                return 'ilXcache';
                break;
            default:
                return 'ilStaticCache';
                break;
        }
    }


    /**
     * @var bool
     */
    protected static $active_cache = array();


    /**
     * @return bool
     */
    public function isActive()
    {
        if (self::$active_cache[$this->getComponent()] !== null) {
            return self::$active_cache[$this->getComponent()];
        }
        if (!self::ACTIVE) {
            self::$active_cache[$this->getComponent()] = false;

            return false;
        }
        if (!$this->getActive()) {
            self::log($this->getComponent() . '-wrapper is inactive...', ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
            self::$active_cache[$this->getComponent()] = false;

            return false;
        }

        $isActive = $this->global_cache->isActive();
        self::log('component ' . $this->getComponent() . ', service is active: '
                  . ($isActive ? 'yes' : 'no'), ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
        self::$active_cache[$this->getComponent()] = $isActive;

        return $isActive;
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function isValid($key)
    {
        return $this->global_cache->isValid($key);
    }


    /**
     * @return bool
     */
    public function isInstallable()
    {
        return count(self::getAllInstallableTypes()) > 0;
    }


    /**
     * @return bool
     */
    public function isCacheServiceInstallable()
    {
        return $this->global_cache->isInstallable();
    }


    /**
     * @return string
     */
    public function getInstallationFailureReason()
    {
        return $this->global_cache->getInstallationFailureReason();
    }


    /**
     * @param $key
     *
     * @throws RuntimeException
     * @return bool
     */
    public function exists($key)
    {
        if (!$this->global_cache->isActive()) {
            return false;
        }

        return $this->global_cache->exists($key);
    }


    /**
     * @param      $key
     * @param      $value
     * @param null $ttl
     *
     * @throws RuntimeException
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        self::log($key . ' set in component ' . $this->getComponent(), ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
        $this->global_cache->setValid($key);

        return $this->global_cache->set($key, $this->global_cache->serialize($value), $ttl);
    }


    /**
     * @param $key
     *
     * @throws RuntimeException
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->isActive()) {
            return false;
        }
        $unserialized_return = $this->global_cache->unserialize($this->global_cache->get($key));
        if ($unserialized_return) {
            $service_name = ' [' . self::lookupServiceClassName($this->getServiceType()) . ']';
            if ($this->global_cache->isValid($key)) {
                self::log($key . ' from component ' . $this->getComponent() . $service_name, ilGlobalCacheSettings::LOG_LEVEL_CHATTY);

                return $unserialized_return;
            } else {
                self::log($key . ' from component ' . $this->getComponent() . ' is invalid' . $service_name, ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
            }
        }

        return null;
    }


    /**
     * @param $key
     *
     * @return bool
     */
    public function delete($key)
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->global_cache->delete($key);
    }


    /**
     * @param bool $complete
     *
     * @return bool
     * @throws RuntimeException
     */
    public function flush($complete = false)
    {
        if ($this->global_cache->isActive()) {
            return $this->global_cache->flush();
        }

        return false;
    }


    public function getInfo()
    {
        return $this->global_cache->getInfo();
    }


    /**
     * @param string $component
     */
    public function setComponent($component)
    {
        $this->component = $component;
    }


    /**
     * @return string
     */
    public function getComponent()
    {
        return $this->component;
    }


    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


    /**
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }


    /**
     * @param int $service_type
     */
    public function setServiceType($service_type)
    {
        if ($this->global_cache instanceof ilGlobalCacheService) {
            $this->global_cache->setServiceType($service_type);
        }
        $this->service_type = $service_type;
    }


    /**
     * @return int
     */
    public function getServiceType()
    {
        if ($this->global_cache instanceof ilGlobalCacheService) {
            return $this->global_cache->getServiceType();
        }

        return $this->service_type;
    }


    /**
     * @return ilGlobalCacheSettings
     */
    public static function getSettings()
    {
        return (self::$settings instanceof ilGlobalCacheSettings ? self::$settings : new ilGlobalCacheSettings());
    }


    /**
     * @param ilGlobalCacheSettings $settings
     */
    public static function setSettings($settings)
    {
        self::$settings = $settings;
    }


    /**
     * @return array
     */
    public static function getActiveComponents()
    {
        return self::$active_components;
    }


    /**
     * @param array $active_components
     */
    public static function setActiveComponents($active_components)
    {
        self::$active_components = $active_components;
    }


    /**
     * @return array
     */
    public static function getAvailableComponents()
    {
        return self::$available_components;
    }


    /**
     * @param array $available_components
     */
    public static function setAvailableComponents($available_components)
    {
        self::$available_components = $available_components;
    }
}
