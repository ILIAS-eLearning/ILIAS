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
 * Class ilGlobalCache
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 1.0.0
 */
class ilGlobalCache
{
    /**
     * @var string
     */
    public const MSG = 'Global Cache not active, can not access cache';
    /**
     * @var bool
     */
    public const ACTIVE = true;
    /**
     * @var int
     */
    public const TYPE_STATIC = 0;
    /**
     * @var int
     */
    public const TYPE_MEMCACHED = 2;
    /**
     * @var int
     */
    public const TYPE_APC = 3;
    /**
     * @var int
     */
    public const TYPE_FALLBACK = self::TYPE_STATIC;
    /**
     * @var string
     */
    public const COMP_CLNG = 'clng';
    /**
     * @var string
     */
    public const COMP_OBJ_DEF = 'obj_def';
    /**
     * @var string
     */
    public const COMP_TEMPLATE = 'tpl';
    /**
     * @var string
     */
    public const COMP_ILCTRL = 'ilctrl';
    /**
     * @var string
     */
    public const COMP_PLUGINS = 'plugins';
    /**
     * @var string
     */
    public const COMP_RBAC_UA = 'rbac_ua';
    /**
     * @var string
     */
    public const COMP_EVENTS = 'events';
    /**
     * @var string
     */
    public const COMP_TPL_BLOCKS = 'tpl_blocks';
    /**
     * @var string
     */
    public const COMP_TPL_VARIABLES = 'tpl_variables';
    /**
     * @var string
     */
    public const COMP_GLOBAL_SCREEN = 'global_screen';
    protected static array $types = array(
        self::TYPE_MEMCACHED,
        self::TYPE_APC,
        self::TYPE_STATIC,
    );
    protected static array $available_types = array(
        self::TYPE_MEMCACHED,
        self::TYPE_APC,
        self::TYPE_STATIC,
    );
    protected static array $active_components = array();
    protected static array $available_components = array(
        self::COMP_CLNG,
        self::COMP_OBJ_DEF,
        self::COMP_ILCTRL,
        self::COMP_TEMPLATE,
        self::COMP_TPL_BLOCKS,
        self::COMP_TPL_VARIABLES,
        self::COMP_EVENTS,
        self::COMP_GLOBAL_SCREEN,
    );
    protected static array $type_per_component = array();
    protected static ?string $unique_service_id = null;
    protected static ?array $instances = null;
    protected \ilGlobalCacheService $global_cache;
    protected ?string $component = null;
    protected bool $active = true;
    protected int $service_type = ilGlobalCache::TYPE_STATIC;
    protected static ?\ilGlobalCacheSettings $settings = null;

    public static function setup(ilGlobalCacheSettings $ilGlobalCacheSettings): void
    {
        self::setSettings($ilGlobalCacheSettings);
        self::setActiveComponents($ilGlobalCacheSettings->getActivatedComponents());
    }

    public static function getInstance(?string $component): \ilGlobalCache
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

    protected function __construct(int $service_type)
    {
        self::generateServiceId();
        $this->setServiceType($service_type);
    }

    protected function initCachingService(): void
    {
        /**
         * @var $ilGlobalCacheService ilGlobalCacheService
         */
        if ($this->getComponent() === '') {
            $this->setComponent('default');
        }
        $serviceName = self::lookupServiceClassName($this->getServiceType());
        $ilGlobalCacheService = new $serviceName(self::$unique_service_id, $this->getComponent());
        $ilGlobalCacheService->setServiceType($this->getServiceType());

        $this->global_cache = $ilGlobalCacheService;
        $this->setActive(in_array($this->getComponent(), self::getActiveComponents()));
    }

    protected function checkSettings(): void
    {
    }

    public static function log(string $message, int $log_level): void
    {
        if ($log_level <= self::getSettings()->getLogLevel()) {
            global $DIC;
            $ilLog = $DIC[\ilLog::class];
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $function = $backtrace[1]['function'];
            $class = $backtrace[1]['class'];
            if ($ilLog instanceof ilComponentLogger) {
                $ilLog->alert($class . '::' . $function . '(): ' . $message);
            }
        }
    }

    protected static function generateServiceId(): string
    {
        if (!isset(self::$unique_service_id)) {
            $raw_service_id = '_';
            if (defined('CLIENT_ID')) {
                $raw_service_id .= 'il_' . CLIENT_ID;
            }
            self::$unique_service_id = substr(md5($raw_service_id), 0, 6);
        }
        return self::$unique_service_id;
    }

    public static function flushAll(): void
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
                self::log(
                    $serviceName . ' returned status ' . ($returned ? 'ok' : 'failure'),
                    ilGlobalCacheSettings::LOG_LEVEL_NORMAL
                );
            }
        }
    }

    public static function getAllInstallableTypes(): array
    {
        $types = array();
        foreach (self::getAllTypes() as $type) {
            if ($type->isCacheServiceInstallable()) {
                $types[] = $type;
            }
        }

        return $types;
    }

    public static function getAllTypes(bool $only_available = true): array
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

    public static function lookupServiceClassName(int $service_type): string
    {
        switch ($service_type) {
            case self::TYPE_APC:
                return ilApc::class;
            case self::TYPE_MEMCACHED:
                return ilMemcache::class;
            default:
                return ilStaticCache::class;
        }
    }

    public static function lookupServiceConfigName(int $service_type): string
    {
        switch ($service_type) {
            case self::TYPE_APC:
                return 'apc';
            case self::TYPE_MEMCACHED:
                return 'memcached';
            default:
                return 'static';
        }
    }

    protected static array $active_cache = array();

    public function isActive(): bool
    {
        $c = $this->getComponent();
        if (isset(self::$active_cache[$c]) && self::$active_cache[$c] !== null) {
            return self::$active_cache[$c];
        }
        if (!self::ACTIVE) {
            self::$active_cache[$c] = false;

            return false;
        }
        if (!$this->getActive()) {
            self::log($c . '-wrapper is inactive...', ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
            self::$active_cache[$c] = false;

            return false;
        }

        $isActive = $this->global_cache->isActive();
        self::log('component ' . $c . ', service is active: '
            . ($isActive ? 'yes' : 'no'), ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
        self::$active_cache[$c] = $isActive;

        return $isActive;
    }

    public function isValid(string $key): bool
    {
        return $this->global_cache->isValid($key);
    }

    public function isInstallable(): bool
    {
        return count(self::getAllInstallableTypes()) > 0;
    }

    public function isCacheServiceInstallable(): bool
    {
        return $this->global_cache->isInstallable();
    }

    public function getInstallationFailureReason(): string
    {
        return $this->global_cache->getInstallationFailureReason();
    }

    /**
     * @throws RuntimeException
     */
    public function exists(string $key): bool
    {
        if (!$this->global_cache->isActive()) {
            return false;
        }

        return $this->global_cache->exists($key);
    }

    /**
     * @param mixed $value
     * @throws RuntimeException
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        if (!$this->isActive()) {
            return false;
        }
        self::log($key . ' set in component ' . $this->getComponent(), ilGlobalCacheSettings::LOG_LEVEL_CHATTY);
        $this->global_cache->setValid($key);

        return $this->global_cache->set($key, $this->global_cache->serialize($value), $ttl);
    }

    /**
     * @return mixed
     * @throws RuntimeException
     */
    public function get(string $key)
    {
        if (!$this->isActive()) {
            return false;
        }
        $unserialized_return = $this->global_cache->unserialize($this->global_cache->get($key));
        if ($unserialized_return) {
            $service_name = ' [' . self::lookupServiceClassName($this->getServiceType()) . ']';
            if ($this->global_cache->isValid($key)) {
                self::log(
                    $key . ' from component ' . $this->getComponent() . $service_name,
                    ilGlobalCacheSettings::LOG_LEVEL_CHATTY
                );

                return $unserialized_return;
            } else {
                self::log(
                    $key . ' from component ' . $this->getComponent() . ' is invalid' . $service_name,
                    ilGlobalCacheSettings::LOG_LEVEL_CHATTY
                );
            }
        }

        return null;
    }

    public function delete(string $key): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->global_cache->delete($key);
    }

    /**
     * @throws RuntimeException
     */
    public function flush(bool $complete = false): bool
    {
        if ($this->global_cache->isActive()) {
            return $this->global_cache->flush($complete);
        }

        return false;
    }

    public function getInfo(): array
    {
        return $this->global_cache->getInfo();
    }

    public function setComponent(string $component): void
    {
        $this->component = $component;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setServiceType(int $service_type): void
    {
        $this->service_type = $service_type;
    }

    public function getServiceType(): int
    {
        return $this->service_type;
    }

    public static function getSettings(): ilGlobalCacheSettings
    {
        return (self::$settings instanceof ilGlobalCacheSettings ? self::$settings : new ilGlobalCacheSettings());
    }

    public static function setSettings(ilGlobalCacheSettings $settings): void
    {
        self::$settings = $settings;
    }

    public static function getActiveComponents(): array
    {
        return self::$active_components;
    }

    public static function setActiveComponents(array $active_components): void
    {
        self::$active_components = $active_components;
    }

    public static function getAvailableComponents(): array
    {
        return self::$available_components;
    }

    public static function setAvailableComponents(array $available_components): void
    {
        self::$available_components = $available_components;
    }
}
