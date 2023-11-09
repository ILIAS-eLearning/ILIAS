<?php

declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Logger settings for setup
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @ingroup ServicesLogging
*/
class ilLoggingSetupSettings implements ilLoggingSettings
{
    private bool $enabled = false;
    private string $log_dir = '';
    private string $log_file = '';


    public function init(): void
    {
        $ilIliasIniFile = new ilIniFile("./ilias.ini.php");
        $ilIliasIniFile->read();

        $enabled = $ilIliasIniFile->readVariable('log', 'enabled');
        $this->enabled = $enabled == '1';
        $this->log_dir = (string) $ilIliasIniFile->readVariable('log', 'path');
        $this->log_file = (string) $ilIliasIniFile->readVariable('log', 'file');
    }

    /**
     * Logging enabled
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLogDir(): string
    {
        return $this->log_dir;
    }

    public function getLogFile(): string
    {
        return $this->log_file;
    }

    /**
     * Get log Level
     * @return int
     */
    public function getLevel(): int
    {
        return ilLogLevel::INFO;
    }

    public function getLevelByComponent(string $a_component_id): int
    {
        return $this->getLevel();
    }

    /**
     * Get log Level
     * @return int
     */
    public function getCacheLevel(): int
    {
        return ilLogLevel::INFO;
    }

    public function isCacheEnabled(): bool
    {
        return false;
    }

    public function isMemoryUsageEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabled(): bool
    {
        return false;
    }

    public function isBrowserLogEnabledForUser(string $a_login): bool
    {
        return false;
    }

    public function getBrowserLogUsers(): array
    {
        return array();
    }
}
