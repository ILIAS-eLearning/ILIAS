<?php

declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @defgroup ServicesLogging Services/Logging
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ingroup ServicesLogging
*/
class ilLoggingDBSettings implements ilLoggingSettings
{
    protected static ?ilLoggingDBSettings $instance = null;

    private bool $enabled = false;
    private ilSetting $storage;

    private int $level;
    private bool $cache = false;
    private int $cache_level;
    private bool $memory_usage = false;
    private bool $browser = false;
    /**
     * @var string[]
     */
    private array $browser_users = array();



    private function __construct()
    {
        $this->enabled = (bool) ILIAS_LOG_ENABLED;
        $this->level = ilLogLevel::INFO;
        $this->cache_level = ilLogLevel::DEBUG;

        $this->storage = new ilSetting('logging');
        $this->read();
    }

    public static function getInstance(): self
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self();
    }

    /**
     * Get level by component
     * @todo better performance
     */
    public function getLevelByComponent(string $a_component_id): int
    {
        $levels = ilLogComponentLevels::getInstance()->getLogComponents();
        foreach ($levels as $level) {
            if ($level->getComponentId() == $a_component_id) {
                if ($level->getLevel()) {
                    return $level->getLevel();
                }
            }
        }
        return $this->getLevel();
    }

    /**
     * @return ilSetting
     */
    protected function getStorage(): ilSetting
    {
        return $this->storage;
    }

    /**
     * Check if logging is enabled
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLogDir(): string
    {
        return ILIAS_LOG_DIR;
    }

    public function getLogFile(): string
    {
        return ILIAS_LOG_FILE;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $a_level): void
    {
        $this->level = $a_level;
    }

    public function setCacheLevel(int $a_level): void
    {
        $this->cache_level = $a_level;
    }

    public function getCacheLevel(): int
    {
        return $this->cache_level;
    }

    public function enableCaching(bool $a_status): void
    {
        $this->cache = $a_status;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cache;
    }

    public function enableMemoryUsage(bool $a_stat): void
    {
        $this->memory_usage = $a_stat;
    }

    public function isMemoryUsageEnabled(): bool
    {
        return $this->memory_usage;
    }

    public function isBrowserLogEnabled(): bool
    {
        return $this->browser;
    }


    public function isBrowserLogEnabledForUser(string $a_login): bool
    {
        if (!$this->isBrowserLogEnabled()) {
            return false;
        }
        if (in_array($a_login, $this->getBrowserLogUsers())) {
            return true;
        }
        return false;
    }

    public function enableBrowserLog(bool $a_stat): void
    {
        $this->browser = $a_stat;
    }

    public function getBrowserLogUsers(): array
    {
        return $this->browser_users;
    }

    public function setBrowserUsers(array $users): void
    {
        $this->browser_users = $users;
    }


    /**
     * Update setting
     */
    public function update(): void
    {
        $this->getStorage()->set('level', (string) $this->getLevel());
        $this->getStorage()->set('cache', (string) $this->isCacheEnabled());
        $this->getStorage()->set('cache_level', (string) $this->getCacheLevel());
        $this->getStorage()->set('memory_usage', (string) $this->isMemoryUsageEnabled());
        $this->getStorage()->set('browser', (string) $this->isBrowserLogEnabled());
        $this->getStorage()->set('browser_users', serialize($this->getBrowserLogUsers()));
    }


    /**
     * Read settings
     *
     * @access private
     */
    private function read(): void
    {
        $this->setLevel((int) $this->getStorage()->get('level', (string) $this->level));
        $this->enableCaching((bool) $this->getStorage()->get('cache', (string) $this->cache));
        $this->setCacheLevel((int) $this->getStorage()->get('cache_level', (string) $this->cache_level));
        $this->enableMemoryUsage((bool) $this->getStorage()->get('memory_usage', (string) $this->memory_usage));
        $this->enableBrowserLog((bool) $this->getStorage()->get('browser', (string) $this->browser));
        $this->setBrowserUsers((array) unserialize($this->getStorage()->get('browser_users', serialize($this->browser_users))));
    }
}
